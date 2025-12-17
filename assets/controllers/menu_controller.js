import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'button']

    connect() {
        console.log('Menu stimulus connecté')
    }

    toggle() {
        this.menuTarget.classList.toggle('hidden')

        if (this.menuTarget.classList.contains('hidden')) {
            this.buttonTarget.textContent = '☰';
        } else {
            this.buttonTarget.textContent = '✕'; 
        }
    }
}
