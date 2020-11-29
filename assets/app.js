/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

import './Sass/app.scss';
import 'bootstrap';
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
import flashy from "./lib/js/flashy.js";
import PostAjaxSearch from './lib/js/PostAjaxSearch';
import LazyLoad from './lib/js/LazyLoad';
import CategorySearchAjax from './lib/js/CategorySearchAjax';

window.flashy = flashy;