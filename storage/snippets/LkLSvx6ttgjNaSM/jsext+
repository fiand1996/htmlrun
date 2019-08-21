const menuItems = document.querySelectorAll('.menu__item');
let menuItemActive = document.querySelector('.menu__item--active');

// ---
// App
// ---

for (var i = 0; i < menuItems.length; i++) {
  menuItems[i].addEventListener('click', buttonClick);
}

// ---------
// Functions
// ---------

function buttonClick() {
  if (!this.classList.contains('menu__item--active')) {
    document.body.style.backgroundColor = `#${this.getAttribute('data-background')}`;

    menuItemActive.classList.remove('menu__item--active');
    this.classList.add('menu__item--active');

    menuItemActive.classList.add('menu__item--animate');
    this.classList.add('menu__item--animate');

    menuItemActive = this;
  }
}