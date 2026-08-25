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

document.querySelectorAll("[data-professional-search]").forEach((component) => {
    const input = component.querySelector("[data-professional-input]");
    const value = component.querySelector("[data-professional-value]");
    const results = component.querySelector("[data-professional-results]");
    const status = component.querySelector("[data-professional-status]");
    const searchUrl = component.dataset.searchUrl;
    let timer;
    let controller;

    const choose = (id, label) => {
        value.value = id;
        input.value = label;
        results.hidden = true;
        status.textContent = `Selecionado: ${label}`;
    };

    const bindOptions = () => {
        results.querySelectorAll("[data-professional-option]").forEach((option) => {
            option.addEventListener("click", () => choose(option.dataset.id, option.dataset.label));
        });
    };

    const render = (professionals) => {
        results.replaceChildren();

        professionals.forEach((professional) => {
            const option = document.createElement("button");
            const name = document.createElement("strong");
            const specialty = document.createElement("span");
            const label = `${professional.first_name} · ${professional.specialty}`;

            option.type = "button";
            option.setAttribute("role", "option");
            option.dataset.professionalOption = "";
            option.dataset.id = professional.id;
            option.dataset.label = label;
            name.textContent = professional.first_name;
            specialty.textContent = professional.specialty;
            option.append(name, specialty);
            results.append(option);
        });

        bindOptions();
        results.hidden = false;
        status.textContent = professionals.length
            ? `${professionals.length} profissional(is) encontrado(s).`
            : "Nenhum profissional ativo encontrado.";
    };

    const search = async () => {
        controller?.abort();
        controller = new AbortController();
        status.textContent = "Pesquisando...";

        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set("q", input.value.trim());
            const response = await fetch(url, {
                headers: { Accept: "application/json" },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error("Falha ao pesquisar profissionais.");
            }

            const payload = await response.json();
            render(payload.data ?? []);
        } catch (error) {
            if (error.name !== "AbortError") {
                results.hidden = true;
                status.textContent = "Não foi possível pesquisar agora.";
            }
        }
    };

    input.addEventListener("input", () => {
        value.value = "";
        window.clearTimeout(timer);
        timer = window.setTimeout(search, 250);
    });
    input.addEventListener("focus", () => {
        if (results.childElementCount > 0) {
            results.hidden = false;
        } else {
            search();
        }
    });
    component.addEventListener("focusout", () => {
        window.setTimeout(() => {
            if (!component.contains(document.activeElement)) {
                results.hidden = true;
            }
        }, 100);
    });

    bindOptions();
});
