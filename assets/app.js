import './bootstrap.js';


import './styles/app.css';
import './styles/font-awesome.css'

import 'bootstrap/dist/css/bootstrap.min.css';

document.addEventListener("turbo:load", function () {
    const btn = document.getElementById('showMedia')
    if (btn) {
        document.getElementById('showMedia').addEventListener('click', (e) => {
            e.target.parentElement.classList.remove('flex')
            e.target.parentElement.classList.add('hidden')

            document.getElementById('medias').classList.remove('hidden')
            document.getElementById('medias').classList.add('flex')
        });
    }
});
