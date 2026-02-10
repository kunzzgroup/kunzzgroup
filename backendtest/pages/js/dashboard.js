
// View Loader
async function loadView() {
    try {
        const response = await fetch(window.BASE_URL + "pages/html/" + window.PAGE_NAME + ".html", {
            credentials: "same-origin"
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const html = await response.text();
        const app = document.getElementById("app");
        // We append instead of replace if sidebar is inside app? 
        // Instruction said: document.getElementById("app").innerHTML = await r.text();
        // But the sidebar is PHP included.
        // If sidebar is OUTSIDE #app, we can safely replace innerHTML.
        app.innerHTML = html;

        // Execute scripts in the loaded HTML if any (classic innerHTML doesn't run scripts, but our shell pattern might rely on separate JS file)
        // Here we are in dashboard.js which IS the separate JS file.

        initDashboard();

    } catch (e) {
        console.error("Failed to load view:", e);
        document.getElementById("app").innerHTML = "<p>Error loading content.</p>";
    }
}

function initDashboard() {
    console.log("Dashboard view loaded.");
    // Initialize any dashboard specific logic here
}

// Start loading
loadView();

// Fetch Rule Enforcement Wrapper (Optional helper, but user said "All frontend API calls must use...")
// We can define a global helper or just ensure we use this pattern.
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
