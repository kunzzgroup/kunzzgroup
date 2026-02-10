window.PAGE_NAME = "dashboard";

async function loadView() {
    try {
        const response = await fetch(window.BASE_URL + "pages/html/" + window.PAGE_NAME + ".html", {
            credentials: "same-origin"
        });
        if (!response.ok) throw new Error("Failed to load view");
        const html = await response.text();
        document.getElementById("app").innerHTML = html;
    } catch (e) {
        console.error("Failed to load view:", e);
        document.getElementById("app").innerHTML = "<p>Error loading content.</p>";
    }
}

window.secureFetch = async (url, data) => {
    return fetch(window.API_BASE + url, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(data)
    });
};

document.addEventListener("DOMContentLoaded", loadView);
