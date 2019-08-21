<?php 

App::addRoute("GET|POST", "/", "Index");
App::addRoute("GET|POST", "/?", "Index");

App::addRoute("GET|POST", "/signup/?", "Signup");
App::addRoute("GET|POST", "/signin/?", "Signin");
App::addRoute("GET|POST", "/signout/?", "Signout");

App::addRoute("GET|POST", "/editor/[a:filename]/?", "Editor");

App::addRoute("GET|POST", "/embed/[a:filename]/?", "Embed");

App::addRoute("GET|POST", "/embedded/[a:filename]/?", "Embedded");

App::addRoute("GET|POST", "/snippets/[a:username]/?", "Snippets");

App::addRoute("GET|POST", "/view/[a:filename]/?", "View");

App::addRoute("GET|POST", "/raw/[a:filename]/?", "Raw");

App::addRoute("GET|POST", "/raw/[a:filename]/[a:action]/?", "Raw");
