import './bootstrap.js';


import './styles/app.css';
import './styles/font-awesome.css'

import 'bootstrap/dist/css/bootstrap.min.css';

document.addEventListener("turbo:load", function () {
    const btnShowMedia = document.getElementById('showMedia')
    if (btnShowMedia) {
        document.getElementById('showMedia').addEventListener('click', (e) => {
            e.target.parentElement.classList.remove('flex')
            e.target.parentElement.classList.add('hidden')

            document.getElementById('medias').classList.remove('hidden')
            document.getElementById('medias').classList.add('flex')
        });
    }

    const btnShowVideoForm = document.getElementById('showVideoForm')
    if (btnShowVideoForm) {
        document.getElementById('showVideoForm').addEventListener('click', (e) => {
            document.getElementById('video-form').classList.remove('hidden')
        });
    }

    const btnCloseVideoForm = document.getElementById('closeVideoForm')
    if (btnCloseVideoForm) {
        document.getElementById('closeVideoForm').addEventListener('click', (e) => {
            document.getElementById('video-form').classList.add('hidden')
        });
    }
});
