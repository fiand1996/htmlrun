import resolveConfig from '@emmetio/config';
import StreamReader from '@emmetio/stream-reader';
import parseFields from '@emmetio/field-parser';
import { isSpace } from '@emmetio/stream-reader-utils';
import extract from '@emmetio/extract-abbreviation';
import { expand, parse, createSnippetsRegistry } from '@emmetio/expand-abbreviation';
import { convertToCSSSnippets } from '@emmetio/css-snippets-resolver';
import parseHTML from '@emmetio/html-matcher';

const editorField = (index, placeholder = '') => `\${${index}${placeholder ? ':' + placeholder : ''}}`;

/**
 * Returns resolved Emmet config for `pos` location of given editor
 * @param  {CodeMirror.Editor} editor
 * @param  {CodeMirror.Position} [pos]  Point in editor where syntax should be detected.
 * Uses `editor.getCursor()` if not given
 * @param  {Object} [options] Additional options to override before config resolve
 * @return {Object}
 */
function createConfig(editor, pos, options) {
	pos = pos || editor.getCursor();
	const syntax = getSyntax(editor, pos);

	/** @type {EmmetConfig} */
	const config = resolveConfig(Object.assign(
		{ field: editorField },
		editor.getOption('emmet'),
		options
	), { syntax });

	const mode = editor.getModeAt(pos);
	if (syntax === 'jsx') {
		config.profile = Object.assign({ selfClosingStyle: 'xml' }, config.profile);
		config.options = Object.assign({ jsx: true }, config.options);
	} else if (mode.name === 'xml') {
		config.profile = Object.assign({ selfClosingStyle: mode.configuration }, config.profile);
	}

	return config;
}

/**
 * Detect Emmet syntax from given editor’s position.
 * @param {CodeMirror.Editor} editor
 * @param {CodeMirror.Position} [pos]
 * @return {String} Returns `null` if Emmet syntax can’t be detected
 */
function getSyntax(editor, pos) {
	const rootMode = editor.getMode();
	if (rootMode.name === 'jsx' || rootMode.name === 'javascript') {
		return rootMode.name;
	}

	const mode = editor.getModeAt(pos);
	return mode.name === 'xml' ? 'html' : mode.name;
}

const LINE_END = 10; // \n

/**
 * A stream reader for CodeMirror editor
 */
class CodeMirrorStreamReader extends StreamReader {
	/**
	 * @param  {CodeMirror.Editor} editor
	 * @param  {CodeMirror.Position} [pos]
	 * @param  {CodeMirror.Range} [limit]
	 */
	constructor(editor, pos, limit) {
		super();
		const CodeMirror = editor.constructor;
		this.editor = editor;
		this.start = this.pos = pos || CodeMirror.Pos(0, 0);

		const lastLine = editor.lastLine();
		this._eof = limit ? limit.to   : CodeMirror.Pos(lastLine, this._lineLength(lastLine));
		this._sof = limit ? limit.from : CodeMirror.Pos(0, 0);
	}

	/**
	 * Returns true only if the stream is at the beginning of the file.
	 * @returns {Boolean}
	 */
	sof() {
		return comparePos(this.pos, this._sof) <= 0;
	}

	/**
	 * Returns true only if the stream is at the end of the file.
	 * @returns {Boolean}
	 */
	eof() {
		return comparePos(this.pos, this._eof) >= 0;
	}

	/**
	 * Creates a new stream instance which is limited to given `start` and `end`
	 * points for underlying buffer
	 * @param  {CodeMirror.Pos} start
	 * @param  {CodeMirror.Pos} end
	 * @return {CodeMirrorStreamReader}
	 */
	limit(from, to) {
		return new this.constructor(this.editor, from, { from, to });
	}

	/**
	 * Returns the next character code in the stream without advancing it.
	 * Will return NaN at the end of the file.
	 * @returns {Number}
	 */
	peek() {
		const { line, ch } = this.pos;
		const lineStr = this.editor.getLine(line);
		return ch < lineStr.length ? lineStr.charCodeAt(ch) : LINE_END;
	}

	/**
	 * Returns the next character in the stream and advances it.
	 * Also returns NaN when no more characters are available.
	 * @returns {Number}
	 */
	next() {
		if (!this.eof()) {
			const code = this.peek();
			this.pos = Object.assign({}, this.pos, { ch: this.pos.ch + 1 });

			if (this.pos.ch >= this._lineLength(this.pos.line)) {
				this.pos.line++;
				this.pos.ch = 0;
			}

			if (this.eof()) {
				// handle edge case where position can move on next line
				// after EOF
				this.pos = Object.assign({}, this._eof);
			}

			return code;
		}

		return NaN;
	}

	/**
	 * Backs up the stream n characters. Backing it up further than the
	 * start of the current token will cause things to break, so be careful.
	 * @param {Number} n
	 */
	backUp(n) {
		const CodeMirror = this.editor.constructor;

		let { line, ch } = this.pos;
		ch -= (n || 1);

		while (line >= 0 && ch < 0) {
			line--;
			ch += this._lineLength(line);
		}

		this.pos = line < 0 || ch < 0
			? CodeMirror.Pos(0, 0)
			: CodeMirror.Pos(line, ch);

		return this.peek();
	}

	/**
	 * Get the string between the start of the current token and the
	 * current stream position.
	 * @returns {String}
	 */
	current() {
		return this.substring(this.start, this.pos);
	}

	/**
	 * Returns contents for given range
	 * @param  {Point} from
	 * @param  {Point} to
	 * @return {String}
	 */
	substring(from, to) {
		return this.editor.getRange(from, to);
	}

	/**
	 * Creates error object with current stream state
	 * @param {String} message
	 * @return {Error}
	 */
	error(message) {
		const err = new Error(`${message} at line ${this.pos.line}, column ${this.pos.ch}`);
		err.originalMessage = message;
		err.pos = this.pos;
		err.string = this.string;
		return err;
	}

	/**
	 * Returns length of given line, including line ending
	 * @param  {Number} line
	 * @return {Number}
	 */
	_lineLength(line) {
		const isLast = line === this.editor.lastLine();
		return this.editor.getLine(line).length + (isLast ? 0 : 1);
	}
}

function comparePos(a, b) {
	return a.line - b.line || a.ch - b.ch;
}

/**
 * Returns token used for single indentation in given editor
 * @param  {CodeMirror.Editor} editor
 * @return {String}
 */
function getIndentation(editor) {
	if (!editor.getOption('indentWithTabs')) {
		return repeatString(' ', editor.getOption('indentUnit'));
	}

	return '\t';
}

/**
 * Normalizes text according to given CodeMirror instance indentation
 * preferences
 * @param  {String} text
 * @param  {CodeMirror.Editor} editor
 * @param  {String} [indentation] Applies `indentText()` with given argument, if provided
 * @return {String}
 */
function normalizeText(editor, text, indentation) {
	let lines = splitByLines(text);
	const indent = getIndentation(editor);

	if (indent !== '\t') {
		lines = lines.map(line => line.replace(/^\t+/,
			tabs => repeatString(indent, tabs.length)));
	}

	if (indentation) {
		lines = lines.map((line, i) => i ? indentation + line : line);
	}

	return lines.join('\n');
}

/**
 * Splits given text by lines
 * @param  {String} text
 * @return {String[]} Lines of text
 */
function splitByLines(text) {
	return Array.isArray(text) ? text : text.split(/\r\n|\r|\n/g);
}

function repeatString(str, count) {
	let result = '';
	while (0 < count--) {
		result += str;
	}

	return result;
}

/**
 * Quick and dirty way to remove fields from given string
 * @param  {String} str
 * @return {String}
 */
function removeFields(str) {
	return parseFields(str).string;
}

/**
 * Check if given range contains point
 * @param  {CodeMirror.Range} range
 * @param  {CodeMirror.Position} pos
 * @param  {Boolean} [exclude] Exclude range and and start
 * @return {Boolean}
 */
function containsPos(range, pos, exclude) {
	return exclude
		? comparePos$1(pos, range.from) > 0 && comparePos$1(pos, range.to) < 0
		: comparePos$1(pos, range.from) >= 0 && comparePos$1(pos, range.to) <= 0;
}

function comparePos$1(a, b) {
	return a.line - b.line || a.ch - b.ch;
}

function rangeFromNode(node) {
	return {
		from: node.start,
		to: node.end
	};
}

/**
 * Narrows given `{from, to}` range to first non-whitespace characters in given 
 * editor content
 * @param {CodeMirror.Editor} editor 
 * @param {CodeMirror.Position} from 
 * @param {CodeMirror.Position} [to] 
 * @returns {Object}
 */
function narrowToNonSpace(editor, from, to) {
	const stream = new CodeMirrorStreamReader(editor, from);

	stream.eatWhile(isSpace);
	from = stream.pos;
	
	if (to) {
		stream.pos = to;
		stream.backUp();

		while (!stream.sof() && isSpace(stream.peek())) {
			stream.backUp();
		}

		stream.next();
		to = stream.pos;
	} else {
		to = from;
	}

	return { from, to };
}

/**
 * Returns nearest CSS property name, left to given position
 * @param {CodeMirror.Editor} editor 
 * @param {CodeMirror.Position} pos 
 * @returns {String}
 */
function getCSSPropertyName(editor, pos) {
	const line = pos.line;
	let ch = pos.ch, token;

	while (ch >= 0) {
		token = editor.getTokenAt({ line, ch });
		if (token.type === 'property') {
			return token.string;
		}

		if (token.start !== ch) {
			ch = token.start;
		} else {
			break;
		}
	}
}

/**
 * Check if given position is inside CSS property value
 * @param {CodeMirror.Editor} editor 
 * @param {CodeMirror.Position} pos 
 * @return {Boolean}
 */
function isCSSPropertyValue(editor, pos) {
	const mode = editor.getModeAt(pos);
	if (mode && mode.name === 'css') {
		const token = editor.getTokenAt(pos);
		const state = token.state && token.state.localState || token.state;
		return state && state.context && state.context.type === 'prop';
	}

	return false;
}

/**
 * Context-aware abbreviation extraction from given editor.
 * Detects syntax context in `pos` editor location and, if it allows Emmet
 * abbreviation to be extracted here, returns object with extracted abbreviation,
 * its location and config.
 * @param {CodeMirror.Editor} editor
 * @param {CodeMirror.Position} pos
 */
function extractAbbreviation(editor, pos, contextAware) {
	const config = createConfig(editor, pos);

	if (contextAware && !canExtract(editor, pos, config)) {
		return null;
	}

	const extracted = extract(editor.getLine(pos.line), pos.ch, {
		lookAhead: true,
		syntax: config.type,
		prefix: config.syntax === 'jsx' && editor.getOption('jsxBracket') ? '<' : ''
	});

	if (extracted) {
		const from = {
			line: pos.line,
			ch: extracted.start
		};
		const to = {
			line: pos.line,
			ch: extracted.end
		};

		if (config.type === 'stylesheet' && contextAware) {
			// In case of stylesheet syntaxes (CSS, LESS) we should narrow down
			// expand context to property value, if possible
			if (isCSSPropertyValue(editor, pos)) {
				config.options = Object.assign({ property: getCSSPropertyName(editor, pos) }, config.options);
			}
		}

		return {
			abbreviation: extracted.abbreviation,
			range: { from, to },
			config
		};
	}
}

/**
 * Check if abbreviation can be extracted from given position
 * @param {CodeMirror.Editor} editor
 * @param {CodeMirror.Position} pos
 * @param {Object} config
 * @return {Boolean}
 */
function canExtract(editor, pos, config) {
	const tokenType = editor.getTokenTypeAt(pos);

	if (config.type === 'stylesheet') {
		return tokenType !== 'comment' && tokenType !== 'string';
	}

	if (config.syntax === 'html') {
		return tokenType === null;
	}

	if (config.syntax === 'slim' || config.syntax === 'pug') {
		return tokenType === null || tokenType === 'tag'
			|| (tokenType && /attribute/.test(tokenType));
	}

	if (config.syntax === 'haml') {
		return tokenType === null || tokenType === 'attribute';
	}

	if (config.syntax === 'jsx') {
		// JSX a bit tricky, delegate it to caller
		return true;
	}

	return false;
}

/**
 * Replaces `range` in `editor` with `text` snippet. A snippet is a string containing
 * tabstops/fields like `${index:placeholder}`: this function will locate such 
 * fields and place cursor at first one.
 * Inserted snippet will be automatically matched with current editor indentation
 * @param {CodeMirror.Editor} editor 
 * @param {CodeMirror.Range} range 
 * @param {String} text
 */
function insertSnippet(editor, range, text) {
	const line = editor.getLine(range.from.line);
	const matchIndent = line.match(/^\s+/);
	let snippet = normalizeText(editor, text, matchIndent && matchIndent[0]);
	const fieldModel = parseFields(snippet);
	
	return editor.operation(() => {
		editor.replaceRange(fieldModel.string, range.from, range.to);

		// Position cursor
		const startIx = editor.indexFromPos(range.from);
		if (fieldModel.fields.length) {
			const field = fieldModel.fields[0];
			const from = editor.posFromIndex(field.location + startIx);
			const to = editor.posFromIndex(field.location + field.length + startIx);
			editor.setSelection(from, to);
		} else {
			editor.setCursor(editor.posFromIndex(startIx + fieldModel.string.length));
		}

		return true;
	});
}

const emmetMarkerClass = 'emmet-abbreviation';

/**
 * Returns parsed abbreviation from given position in `editor`, if possible.
 * @param {CodeMirror.Editor} editor
 * @param {CodeMirror.Position} pos
 * @param {Boolean} [contextAware] Use context-aware abbreviation detection
 * @returns {Abbreviation}
 */
function abbreviationFromPosition(editor, pos, contextAware) {
	// Try to find abbreviation marker from given position
	const marker = findMarker(editor, pos);
	if (marker && marker.model) {
		return marker.model;
	}

	// Try to extract abbreviation from given position
	const extracted = extractAbbreviation(editor, pos, contextAware);
	if (extracted) {
		try {
			const abbr = new Abbreviation(extracted.abbreviation, extracted.range, extracted.config);
			return abbr.valid(editor, contextAware) ? abbr : null;
		} catch (err) {
			// skip
			// console.warn(err);
		}
	}
}

/**
 * Returns *valid* Emmet abbreviation marker (if any) for given position of editor
 * @param  {CodeMirror.Editor} editor
 * @param  {CodeMirror.Position} [pos]
 * @return {CodeMirror.TextMarker}
 */
function findMarker(editor, pos) {
	const markers = editor.findMarksAt(pos);
	for (let i = 0, marker; i < markers.length; i++) {
		marker = markers[i];
		if (marker.className === emmetMarkerClass) {
			if (isValidMarker(editor, marker)) {
				return marker;
			}

			marker.clear();
		}
	}
}

/**
 * Removes Emmet abbreviation markers from given editor
 * @param {CodeMirror.Editor} editor
 */
function clearMarkers(editor) {
	const markers = editor.getAllMarks();
	for (let i = 0; i < markers.length; i++) {
		if (markers[i].className === emmetMarkerClass) {
			markers[i].clear();
		}
	}
}

/**
 * Marks Emmet abbreviation for given editor position, if possible
 * @param  {CodeMirror.Editor} editor Editor where abbreviation marker should be created
 * @param  {Abbreviation} model Parsed abbreviation model
 * @return {CodeMirror.TextMarker} Returns `undefined` if no valid abbreviation under caret
 */
function createMarker(editor, model) {
	const { from, to } = model.range;
	const marker = editor.markText(from, to, {
		inclusiveLeft: true,
		inclusiveRight: true,
		clearWhenEmpty: true,
		className: emmetMarkerClass
	});
	marker.model = model;
	return marker;
}

/**
 * Ensures that given editor Emmet abbreviation marker contains valid Emmet abbreviation
 * and updates abbreviation model if required
 * @param {CodeMirror} editor
 * @param {CodeMirror.TextMarket} marker
 * @return {Boolean} `true` if marker contains valid abbreviation
 */
function isValidMarker(editor, marker) {
	const range = marker.find();

	// No newlines inside abbreviation
	if (range.from.line !== range.to.line) {
		return false;
	}

	// Make sure marker contains valid abbreviation
	let text = editor.getRange(range.from, range.to);
	if (!text || /^\s|\s$/g.test(text)) {
		return false;
	}

	if (marker.model && marker.model.config.syntax === 'jsx' && text[0] === '<') {
		text = text.slice(1);
	}

	if (!marker.model || marker.model.abbreviation !== text) {
		// marker contents was updated, re-parse abbreviation
		try {
			marker.model = new Abbreviation(text, range, marker.model.config);
			if (!marker.model.valid(editor, true)) {
				marker.model = null;
			}
		} catch (err) {
			console.warn(err);
			marker.model = null;
		}
	}

	return Boolean(marker.model && marker.model.snippet);
}

class Abbreviation {
	/**
	 * @param {String} abbreviation Abbreviation string
	 * @param {CodeMirror.Range} range Abbreviation location in editor
	 * @param {Object} [config]
	 */
	constructor(abbreviation, range, config) {
		this.abbreviation = abbreviation;
		this.range = range;
		this.config = config;
		this.ast = parse(abbreviation, config);
		this.snippet = expand(this.ast, config);
		this.preview = removeFields(this.snippet);
	}

	/**
	 * Inserts current expanded abbreviation into given `editor` by replacing
	 * `range`
	 * @param {CodeMirror.Editor} editor
	 * @param {CodeMirror.Range} [range]
	 */
	insert(editor, range) {
		return insertSnippet(editor, range || this.range, this.snippet);
	}

	/**
	 * Check if parsed abbreviation is valid
	 * @param {Boolean} [contextAware] Perform context-aware validation: ensure 
	 * that expanded result is expected at abbreviation location
	 */
	valid(editor, contextAware) {
		if (this.preview && this.abbreviation !== this.preview) {
			return contextAware && this.config.type === 'stylesheet'
				? this._isValidForStylesheet(editor)
				: true;
		}

		return false;
	}

	_isValidForStylesheet(editor) {
		const pos = this.range.from;
		const token = editor.getTokenAt(pos);

		if (/^[#!]/.test(this.abbreviation)) {
			// Abbreviation is a property value
			return isCSSPropertyValue(editor, pos);
		}

		// All expanded nodes are properties? Properties has names, regular snippets don’t.
		const isProperty = this.ast.children.every(node => node.name);
		const state = token.state && token.state.localState || token.state;

		if (isProperty) {
			// Expanded abbreviation consists of properties: make sure we’re inside 
			// block context
			// NB: in Sass, no actual block context since it’s indetation-based
			return this.config.syntax === 'sass' 
				|| (state && state.context && state.context.type === 'block');
		}

		// Expanded abbreviations are basic snippets: allow them everywhere, but forbid
		// if expanded result equals abbreviation (meaningless).
		return true;
	}
}

/**
 * Expand abbreviation command
 * @param {CodeMirror.Editor} editor
 * @param {Boolean} contextAware
 */
function expandAbbreviation(editor, contextAware) {
	if (editor.somethingSelected()) {
		return editor.constructor.Pass;
	}

	const abbr = abbreviationFromPosition(editor, editor.getCursor(), contextAware);

	if (abbr) {
		abbr.insert(editor);
		clearMarkers(editor);
		return true;
	}

	// If no abbreviation was expanded, allow editor to handle different
	// action for keyboard shortcut (Tab key mostly)
	return editor.constructor.Pass;
}

function emmetInsertLineBreak(editor) {
	const between = editor.listSelections().map(sel => betweenTags(editor, sel));

	if (!between.some(Boolean)) {
		return editor.constructor.Pass;
	}

	editor.operation(() => {
		let sels = editor.listSelections();
		const singleSep = editor.doc.lineSeparator();
		const doubleSep = singleSep + singleSep;

		// Step 1: insert newlines either single or double depending on selection
		for (let i = sels.length - 1; i >= 0; i--) {
			editor.replaceRange(between[i] ? doubleSep : singleSep, sels[i].anchor, sels[i].head, '+newline');
		}

		// Step 2: indent inserted lines
		sels = editor.listSelections();
		for (let i = 0; i < sels.length; i++) {
			editor.indentLine(sels[i].from().line, null, true);

			if (between[i]) {
				editor.indentLine(sels[i].from().line - 1, null, true);
			}
		}

		// Step 3: adjust caret positions
		editor.setSelections(editor.listSelections().map((sel, i) => {
			if (between[i]) {
				const line = sel.from().line - 1;
				const cursor = {
					line,
					ch: editor.getLine(line).length
				};
				return { anchor: cursor, head: cursor };
			}

			return sel;
		}));
	});
}

/**
 * Check if given range is a single caret between tags
 * @param {CodeMirror} editor
 * @param {CodeMirror.range} range
 */
function betweenTags(editor, range) {
	if (equalCursorPos(range.anchor, range.head)) {
		const cursor = range.anchor;
		const mode = editor.getModeAt(cursor);

		if (mode.name === 'xml') {
			const left = editor.getTokenAt(cursor);
			const right = editor.getTokenAt(Object.assign({}, cursor, { ch: cursor.ch + 1 }));

			return left.type === 'tag bracket' && left.string === '>'
				&& right.type === 'tag bracket' && right.string === '</';
		}
	}
}

// Compare two positions, return 0 if they are the same, a negative
// number when a is less, and a positive number otherwise.
function cmp(a, b) {
	return a.line - b.line || a.ch - b.ch;
}

function equalCursorPos(a, b) {
	return a.sticky === b.sticky && cmp(a, b) === 0;
}

/**
 * Marks selected text or matched node content with abbreviation
 * @param {CodeMirror} editor 
 */
function wrapWithAbbreviation(editor) {
	const range = getWrappingContentRange(editor);

	if (range) {
		const prompt = editor.getOption('emmetPrompt') || defaultPrompt;
		const text = editor.getRange(range.from, range.to, '\n')
			.split('\n')
			.map(line => line.trim());

		prompt(editor, 'Enter abbreviation to wrap with:', abbr => {
			if (abbr) {
				const model = new Abbreviation(abbr, range, createConfig(editor, range.from, { text }));
				model.insert(editor);
			}
		});
	} else {
		console.warn('Nothing to wrap');
	}
}

/**
 * Returns content range that should be wrapped
 * @param {CodeMirror} editor 
 */
function getWrappingContentRange(editor) {
	if (editor.somethingSelected()) {
		const sel = editor.listSelections().filter(sel => sel.anchor !== sel.head)[0];
		if (sel) {
			return  comparePos$1(sel.anchor, sel.head) < 0
				? { from: sel.anchor, to: sel.head }
				: { from: sel.head, to: sel.anchor };
		}
	}

	// Nothing selected, find parent HTML node and return range for its content
	return getTagRangeForPos(editor, editor.getCursor());
}

/**
 * Returns either inner or outer tag range (depending on `pos` location) 
 * for given position
 * @param {CodeMirror} editor 
 * @param {Object} pos 
 * @return {Object}
 */
function getTagRangeForPos(editor, pos) {
	const model = editor.getEmmetDocumentModel();
	const tag = model && model.nodeForPoint(pos);

	if (!tag) {
		return null;
	}

	// Depending on given position, return either outer or inner tag range
	if (inRange(tag.open, pos) || inRange(tag.close, pos)) {
		// Outer range
		return rangeFromNode(tag);
	}

	// Inner range
	const from = tag.open.end;
	const to = tag.close ? tag.close.start : tag.open.end;

	return narrowToNonSpace(editor, from, to);
}

function inRange(tag, pos) {
	return tag && containsPos(rangeFromNode(tag), pos);
}

function defaultPrompt(editor, message, callback) {
	callback(window.prompt(message));
}

/**
 * Marks Emmet abbreviation for given editor position, if possible
 * @param  {CodeMirror.Editor} editor Editor where abbreviation marker should be created
 * @param  {CodeMirror.Position} pos Editor position where abbreviation marker
 * should be created. Abbreviation will be automatically extracted from given position
 * @return {CodeMirror.TextMarker} Returns `undefined` if no valid abbreviation under caret
 */
function markAbbreviation(editor, pos) {
	const marker = findMarker(editor, pos);
	if (marker) {
		// there’s active marker with valid abbreviation
		return marker;
	}

	// No active marker: remove previous markers and create new one, if possible
	clearMarkers(editor);

	const model = abbreviationFromPosition(editor, pos, true);

	if (model) {
		return createMarker(editor, model);
	}
}

/**
 * Returns available completions from given editor
 * @param  {CodeMirror.Editor} editor
 * @param  {Abbreviation} abbrModel Parsed Emmet abbreviation model for which
 * completions should be populated
 * @param  {CodeMirror.Position} abbrPos Abbreviation location in editor
 * @param  {CodeMirror.Position} [pos] Cursor position in editor
 * @return {EmmetCompletion[]}
 */
function autocompleteProvider(editor, pos) {
	pos = pos || editor.getCursor();
	let completions = [];

	// Provide two types of completions:
	// 1. Expanded abbreviation
	// 2. Snippets

	const abbreviation = abbreviationFromPosition(editor, pos, true);
	// NB: Check for edge case: expanded abbreviation equals to original
	// abbreviation (for example, `li.item` expands to `li.item` in Slim),
	// no need to provide completion for this case
	if (abbreviation && abbreviation.abbreviation !== abbreviation.snippet) {
		completions.push(expandedAbbreviationCompletion(editor, pos, abbreviation));
	}

	const config = abbreviation ? abbreviation.config : createConfig(editor, pos);

	if (config.type === 'stylesheet') {
		completions = completions.concat(getStylesheetCompletions(editor, pos, config));
	} else {
		completions = completions.concat(getMarkupCompletions(editor, pos, config));
	}

	return {
		type: config.type,
		syntax: config.syntax,
		abbreviation,
		completions: completions.filter(Boolean)
	};
}

/**
 * Returns completions for markup syntaxes (HTML, Slim, Pug etc.)
 * @param  {CodeMirror} editor
 * @param  {CodeMirror.Position} pos Cursor position in editor
 * @param  {Object} config Resolved Emmet config
 * @return {EmmetCompletion[]}
 */
function getMarkupCompletions(editor, pos, config) {
	const line = editor.getLine(pos.line).slice(0, pos.ch);
	const prefix = extractPrefix(line, /[\w:\-$@]/);

	// Make sure that current position precedes element name (e.g. not attribute,
	// class, id etc.)
	if (prefix) {
		const prefixRange = {
			from: { line: pos.line, ch: pos.ch - prefix.length },
			to: pos
		};

		return getSnippetCompletions(editor, pos, config)
			.filter(completion => completion.key !== prefix && completion.key.indexOf(prefix) === 0)
			.map(completion => new EmmetCompletion('snippet', editor, prefixRange, completion.key, completion.preview, completion.snippet));
	}

	return [];
}

/**
 * Returns completions for stylesheet syntaxes
 * @param  {CodeMirror} editor
 * @param  {CodeMirror.Position} pos Cursor position in editor
 * @param  {Object} config Resolved Emmet config
 * @return {EmmetCompletion[]}
 */
function getStylesheetCompletions(editor, pos, config) {
	const line = editor.getLine(pos.line).slice(0, pos.ch);
	const prefix = extractPrefix(line, /[\w-@$]/);

	if (prefix) {
		// Make sure that current position precedes element name (e.g. not attribute,
		// class, id etc.)
		const prefixRange = {
			from: { line: pos.line, ch: pos.ch - prefix.length },
			to: pos
		};

		if (config.options && config.options.property) {
			const lowerProp = config.options.property.toLowerCase();
			// Find matching CSS property snippet for keyword completions
			const completion = getSnippetCompletions(editor, pos, config)
				.find(item => item.property && item.property === lowerProp);

			if (completion && completion.keywords.length) {
				return completion.keywords.map(kw => {
					return kw.key.indexOf(prefix) === 0 && new EmmetCompletion('value', editor, prefixRange, kw.key, kw.preview, kw.snippet);
				}).filter(Boolean);
			}
		} else {
			return getSnippetCompletions(editor, pos, config)
				.filter(completion => completion.key !== prefix && completion.key.indexOf(prefix) === 0)
				.map(completion => new EmmetCompletion('snippet', editor, prefixRange, completion.key, completion.preview, completion.snippet));
		}
	}

	return [];
}

/**
 * Returns all possible snippets completions for given editor context.
 * Completions are cached in editor for for re-use
 * @param  {CodeMirror.Editor} editor
 * @param  {CodeMirror.Position} pos
 * @param  {Object} config
 * @return {Array}
 */
function getSnippetCompletions(editor, pos, config) {
	const { type, syntax } = config;

	if (!editor.state.emmetCompletions) {
		editor.state.emmetCompletions = {};
	}

	const cache = editor.state.emmetCompletions;

	if (!(syntax in cache)) {
		const registry = createSnippetsRegistry(type, syntax, config.snippets);

		cache[syntax] = type === 'stylesheet'
			? getStylesheetSnippets(registry, config)
			: getMarkupSnippets(registry, config);
	}

	return cache[syntax];
}

/**
 * Returns stylesheet snippets list
 * @param {SnippetsRegistry} registry
 * @return {Array}
 */
function getStylesheetSnippets(registry) {
	return convertToCSSSnippets(registry).map(snippet => {
		let preview = snippet.property;
		const keywords = snippet.keywords();
		if (keywords.length) {
			preview += `: ${removeFields(keywords.join(' | '))}`;
		} else if (snippet.value) {
			preview += `: ${removeFields(snippet.value)}`;
		}

		return {
			key: snippet.key,
			value: snippet.value,
			snippet: snippet.key,
			property: snippet.property,
			keywords: keywords.map(kw => {
				const m = kw.match(/^[\w-]+/);
				return m && {
					key: m[0],
					preview: removeFields(kw),
					snippet: kw
				};
			}).filter(Boolean),
			preview
		};
	});
}

/**
 * Returns markup snippets list
 * @param {SnippetsRegistry} registry
 * @param {Object} config
 * @return {Array}
 */
function getMarkupSnippets(registry, config) {
	return registry.all({ type: 'string' }).map(snippet => ({
		key: snippet.key,
		value: snippet.value,
		preview: removeFields(expand(snippet.value, config)),
		snippet: snippet.key
	}));
}

function expandedAbbreviationCompletion(editor, pos, abbrModel) {
	let preview = abbrModel.preview;
	if (preview.length > 500) {
		preview = preview.slice(0, 500) + '...';
	}

	return new EmmetCompletion('expanded-abbreviation', editor, abbrModel.range,
		'Expand abbreviation', preview, (editor, range) => abbrModel.insert(editor, range));
}

/**
 * Extracts prefix from the end of given string that matches `match` regexp
 * @param {String} str
 * @param {RegExp} match
 * @return {String} Extracted prefix
 */
function extractPrefix(str, match) {
	let offset = str.length;

	while (offset > 0) {
		if (!match.test(str[offset - 1])) {
			break;
		}
		offset--;
	}

	return str.slice(offset);
}

class EmmetCompletion {
	/**
	 * @param {String} type
	 * @param {CodeMirror.Editor} editor
	 * @param {CodeMirror.Range} range
	 * @param {String} name
	 * @param {String} preview
	 * @param {Function} snippet
	 */
	constructor(type, editor, range, name, preview, snippet) {
		this.type = type;
		this.editor = editor;
		this.range = range;
		this.name = name;
		this.preview = preview;
		this.snippet = snippet;

		this._inserted = false;
	}

	insert() {
		if (!this._inserted) {
			this._inserted = true;
			if (typeof this.snippet === 'function') {
				this.snippet(this.editor, this.range);
			} else {
				insertSnippet(this.editor, this.range, this.snippet);
			}
			clearMarkers(this.editor);
		}
	}
}

/**
 * A syntax-specific model container, used to get unified access to underlying
 * parsed document
 */
class SyntaxModel {
	/**
	 * @param  {Object} dom      Parsed document tree
	 * @param  {String} type     Type of document (html, stylesheet, etc.)
	 * @param  {String} [syntax] Optional document syntax like html, xhtml or xml
	 */
	constructor(dom, type, syntax) {
		this.dom = dom;
		this.type = type;
		this.syntax = syntax;
	}

	/**
	 * Returns best matching node for given point
	 * @param  {CodeMirror.Pos}   pos
	 * @param  {Boolean} [exclude] Exclude node’s start and end positions from
	 *                             search
	 * @return {Node}
	 */
	nodeForPoint(pos, exclude) {
		let ctx = this.dom.firstChild;
		let found = null;

		while (ctx) {
			if (containsPos(rangeFromNode(ctx), pos, exclude)) {
				// Found matching tag. Try to find deeper, more accurate match
				found = ctx;
				ctx = ctx.firstChild;
			} else {
				ctx = ctx.nextSibling;
			}
		}

		return found;
	}
}

/**
 * Creates DOM-like model for given text editor
 * @param  {CodeMirror} editor
 * @param  {String}     syntax
 * @return {Node}
 */
function create(editor, syntax) {
	const stream = new CodeMirrorStreamReader(editor);
	const xml = syntax === 'xml';

	try {
		return new SyntaxModel(parseHTML(stream, { xml }), 'html', syntax || 'html');
	} catch (err) {
		console.warn(err);
	}
}

function getModel(editor) {
	const syntax = getSyntax$1(editor);
	return create(editor, syntax);
}

function getCachedModel(editor) {
	if (!editor.state._emmetModel) {
		editor.state._emmetModel = getModel(editor);
	}

	return editor.state._emmetModel;
}

function resetCachedModel(editor) {
	editor.state._emmetModel = null;
}

/**
 * Returns parser-supported syntax of given editor (like 'html', 'css' etc.).
 * Returns `null` if editor’s syntax is unsupported
 * @param  {CodeMirror} editor
 * @return {String}
 */
function getSyntax$1(editor) {
	const mode = editor.getMode();

	if (mode.name === 'htmlmixed') {
		return 'html';
	}

	return mode.name === 'xml' ? mode.configuration : mode.name;
}

const openTagMark = 'emmet-open-tag';
const closeTagMark = 'emmet-close-tag';

/**
 * Finds matching tag pair for given position in editor
 * @param  {CodeMirror.Editor} editor
 * @param  {CodeMirror.Position} pos
 * @return {Object}
 */
function matchTag(editor, pos) {
	pos = pos || editor.getCursor();

	// First, check if there are tag markers in editor
	const marked = getMarkedTag(editor);

	// If marks found, validate them: make sure cursor is either in open
	// or close tag
	if (marked) {
		if (containsPos(marked.open.find(), pos)) {
			// Point is inside open tag, make sure if there’s a closing tag,
			// it matches open tag content
			if (!marked.close || text(editor, marked.open) === text(editor, marked.close)) {
				return marked;
			}
		} else if (marked.close) {
			// There’s a close tag, make sure pointer is inside it and it matches
			// open tag
			if (containsPos(marked.close.find(), pos) && text(editor, marked.open) === text(editor, marked.close)) {
				return marked;
			}
		}
	}
	
	// Markers are not valid anymore, remove them
	clearTagMatch(editor);

	// Find new tag pair from parsed HTML model and mark them
	const node = findTagPair(editor, pos);
	if (node && node.type === 'tag') {
		return {
			open: createTagMark(editor, node.open.name, openTagMark),
			close: node.close && createTagMark(editor, node.close.name, closeTagMark)
		};
	}
}

function getMarkedTag(editor) {
	let open, close;
	editor.getAllMarks().forEach(mark => {
		if (mark.className === openTagMark) {
			open = mark;
		} else if (mark.className === closeTagMark) {
			close = mark;
		}
	});

	return open ? { open, close } : null;
}

/**
 * Removes all matched tag pair markers from editor
 * @param  {CodeMirror.Editor} editor
 */
function clearTagMatch(editor) {
	editor.getAllMarks().forEach(mark => {
		if (mark.className === openTagMark || mark.className === closeTagMark) {
			mark.clear();
		}
	});
}

/**
 * Finds tag pair (open and close, if any) form parsed HTML model of given editor
 * @param  {CodeMirror.Editor} editor
 * @param  {CodeMirror.Position} pos
 * @return {Object}
 */
function findTagPair(editor, pos) {
	const model = editor.getEmmetDocumentModel();
	return model && model.nodeForPoint(pos || editor.getCursor());
}

function createTagMark(editor, tag, className) {
	return editor.markText(tag.start, tag.end, {
		className,
		inclusiveLeft: true,
		inclusiveRight: true,
		clearWhenEmpty: false
	});
}

function text(editor, mark) {
	const range = mark.find();
	return range ? editor.getRange(range.from, range.to) : '';
}

function renameTag(editor, obj) {
	const tag = getMarkedTag(editor);
	const pos = obj.from;

	if (!tag) {
		return;
	}

	if (containsPos(tag.open.find(), pos) && tag.close) {
		// Update happened inside open tag, update close tag as well
		updateTag(editor, tag.open, tag.close);
	} else if (tag.close && containsPos(tag.close.find(), pos)) {
		// Update happened inside close tag, update open tag as well
		updateTag(editor, tag.close, tag.open);
	}
}

function updateTag(editor, source, dest) {
	const name = text$1(editor, source);
	const range = dest.find();
	const m = name.match(/[\w:.-]+/);
	const newName = !name ? '' : (m && m[0]);

	if (newName != null) {
		if (editor.getRange(range.from, range.to) !== newName) {
			editor.replaceRange(newName, range.from, range.to);
		}
	} else {
		// User entered something that wasn’t a valid tag name.
		clearTagMatch(editor);
	}
}

function text$1(editor, mark) {
	const range = mark.find();
	return range ? editor.getRange(range.from, range.to) : '';
}

/**
 * Registers Emmet extension on given CodeMirror constructor.
 * This file is designed to be imported somehow into the app (CommonJS, ES6,
 * Rollup/Webpack/whatever). If you simply want to add a <script> into your page
 * that registers Emmet extension on global CodeMirror constructor, use
 * `browser.js` instead
 */
function registerEmmetExtension(CodeMirror) {
	// Register Emmet commands
	Object.assign(CodeMirror.commands, {
		emmetExpandAbbreviation: editor => expandAbbreviation(editor, true),
		emmetExpandAbbreviationAll: editor => expandAbbreviation(editor, false),
		emmetInsertLineBreak,
		emmetWrapWithAbbreviation: wrapWithAbbreviation
	});
	const markOnEditorChange = editor => markAbbreviation(editor, editor.getCursor());

	// Defines options that allows abbreviation marking in text editor
	CodeMirror.defineOption('markEmmetAbbreviation', true, (editor, value) => {
		if (value) {
			editor.on('change', markOnEditorChange);
		} else {
			editor.off('change', markOnEditorChange);
			clearMarkers(editor);
		}
	});

	CodeMirror.defineOption('autoRenameTags', true, (editor, value) => {
		value ? editor.on('change', renameTag) : editor.off('change', renameTag);
	});

	// Enable/disable leading angle bracket for JSX abbreviations
	CodeMirror.defineOption('jsxBracket', true);

	CodeMirror.defineOption('markTagPairs', false, (editor, value) => {
		if (value) {
			editor.on('cursorActivity', matchTag);
			editor.on('change', resetCachedModel);
		} else {
			editor.off('cursorActivity', matchTag);
			editor.off('change', resetCachedModel);
			resetCachedModel(editor);
			clearTagMatch(editor);
		}
	});

	// Emmet config: https://github.com/emmetio/config
	CodeMirror.defineOption('emmet', {});

	/**
	 * Returns Emmet completions for context from `pos` position.
	 * Abbreviations are calculated for marked abbreviation at given position.
	 * If no parsed abbreviation marker is available and `force` argument is
	 * given, tries to mark abbreviation and populate completions list again.
	 * @param  {CodeMirror.Position} [pos]
	 * @param  {Boolean} [force]
	 * @return {EmmetCompletion[]}
	 */
	CodeMirror.defineExtension('getEmmetCompletions', function(pos, force) {
		const editor = this;
		if (typeof pos === 'boolean') {
			force = pos;
			pos = null;
		}

		pos = pos || editor.getCursor();

		const autocomplete = autocompleteProvider(editor, pos);
		if (autocomplete && autocomplete.completions.length) {
			if (editor.getOption('markEmmetAbbreviation')) {
				// Ensure abbreviation marker exists
				if (!findMarker(editor, pos) && force) {
					clearMarkers(editor);
					createMarker(autocomplete.model);
				}
			}

			return {
				from: autocomplete.abbreviation.range.from,
				to: autocomplete.abbreviation.range.to,
				list: autocomplete.completions
			};
		}
	});

	/**
	 * Returns valid Emmet abbreviation and its location in editor from given
	 * position
	 * @param  {CodeMirror.Pos} [pos] Position from which abbreviation should be
	 * extracted. If not given, current cursor position is used
	 * @return {Abbreviation}
	 */
	CodeMirror.defineExtension('getEmmetAbbreviation', function(pos, contextAware) {
		return abbreviationFromPosition(this, pos || this.getCursor(), contextAware);
	});

	CodeMirror.defineExtension('findEmmetMarker', function(pos) {
		return findMarker(this, pos || this.getCursor());
	});

	CodeMirror.defineExtension('getEmmetDocumentModel', function() {
		const editor = this;
		return editor.getOption('markTagPairs')
			? getCachedModel(editor)
			: getModel(editor);
	});
}

export default registerEmmetExtension;
//# sourceMappingURL=emmet-codemirror-plugin.es.js.map
