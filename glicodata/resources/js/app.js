import "./bootstrap";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

document.addEventListener("submit", (event) => {
    const message = event.target.dataset.confirm;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

document.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-confirm]");

    if (button && !window.confirm(button.dataset.confirm)) {
        event.preventDefault();
    }
});
