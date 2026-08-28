import tailwindcss from "tailwindcss";

let autoprefixer = null;

try {
    const mod = await import("autoprefixer");
    autoprefixer = mod.default;
} catch {
    // Keep dev server running even when autoprefixer isn't installed yet.
}

const plugins = [tailwindcss()];

if (autoprefixer) {
    plugins.push(autoprefixer());
}

export default { plugins };
