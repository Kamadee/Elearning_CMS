import './bootstrap';
import * as FilePond from 'filepond';

require('./bootstrap');

$(document).ready(function () {
    // executes when HTML-Document is loaded and DOM is ready
    console.log("Hi 👀");

    const inputElement = document.querySelector('input[type="file"]');
    const pond = FilePond.create(inputElement);
});