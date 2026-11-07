const fs = require('fs');
const path = require('path');

const viewsDir = 'e:/jibas/mayantara-presensi/resources/views';

// Common replacements for all blade files (except layout and dashboard which are already done)
const replacements = [
    // Card containers: white bg -> glass effect
    ['bg-white rounded-2xl shadow-sm border border-slate-100', 'bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100'],
    ['bg-white rounded-xl shadow-sm border border-slate-100', 'bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-sky-100'],
    // Table header rows
    ['bg-slate-50/50 border-b border-slate-100 text-slate-500', 'bg-sky-50/50 border-b border-sky-100 text-slate-500'],
    // Table body dividers
    ['divide-y divide-slate-100 text-sm', 'divide-y divide-sky-50 text-sm'],
    // Row hover
    ['hover:bg-slate-50/50 transition-colors', 'hover:bg-sky-50/30 transition-colors'],
    // Modal overlay
    ['bg-slate-900/50 backdrop-blur-sm', 'bg-sky-900/40 backdrop-blur-sm'],
    // Modal header bar
    ['border-b border-slate-100 flex items-center justify-between bg-slate-50', 'border-b border-sky-100 flex items-center justify-between bg-sky-50/50'],
    ['bg-slate-50 px-4 py-3', 'bg-sky-50/50 px-4 py-3'],
    ['bg-slate-50 px-6 py-4', 'bg-sky-50/30 px-6 py-4'],
    ['bg-slate-50 px-6 py-3', 'bg-sky-50/30 px-6 py-3'],
    // Form footer
    ['bg-slate-50 px-8 py-5 border-t border-slate-100', 'bg-sky-50/30 px-8 py-5 border-t border-sky-100'],
    // Section header backgrounds
    ['border-b border-slate-100 flex items-center justify-between bg-slate-50/50', 'border-b border-sky-100 flex items-center justify-between bg-sky-50/30'],
    // Primary action buttons: indigo -> sky gradient
    ['bg-indigo-600 hover:bg-indigo-700', 'bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700'],
    ['shadow-indigo-200', 'shadow-sky-200'],
    // Secondary amber buttons stay but update shadow
    ['shadow-amber-200', 'shadow-amber-300/40'],
    // Accent colors for links and interactive elements
    ['text-indigo-600 focus:ring-indigo-500', 'text-sky-600 focus:ring-sky-500'],
    ['text-indigo-600 hover:text-indigo-800', 'text-sky-600 hover:text-sky-700'],
    ['text-indigo-500 hover:bg-indigo-50', 'text-sky-500 hover:bg-sky-50'],
    ['bg-indigo-50 text-indigo-600', 'bg-sky-50 text-sky-600'],
    ['bg-indigo-100 text-indigo-600', 'bg-sky-100 text-sky-600'],
    ['border-indigo-100', 'border-sky-100'],
    ['bg-indigo-50', 'bg-sky-50'],
    // Focus rings
    ['focus:ring-amber-500/20 focus:border-amber-500', 'focus:ring-sky-400/30 focus:border-sky-400'],
    // Checkbox colors
    ['text-indigo-600 focus:ring-indigo-500', 'text-sky-600 focus:ring-sky-500'],
    // Bulk action bar
    ['bg-rose-50 px-6 py-3 border-b border-rose-100', 'bg-rose-50/80 px-6 py-3 border-b border-rose-100'],
    // Input borders
    ['border border-slate-200 rounded-xl', 'border border-sky-200 rounded-xl'],
    ['border border-slate-200 rounded-lg', 'border border-sky-200 rounded-lg'],
    // emerald button keep but update
    ['bg-emerald-600 hover:bg-emerald-700', 'bg-emerald-500 hover:bg-emerald-600'],
    // Page title style
    ['text-2xl font-bold text-slate-800 tracking-tight', 'text-xl font-extrabold text-slate-800 tracking-tight'],
];

// Skip these files (already updated)
const skipFiles = [
    'layouts/app.blade.php',
    'dashboard.blade.php',
    'presensi/scan.blade.php',
    'auth/login.blade.php',
];

function processDir(dir) {
    const entries = fs.readdirSync(dir);
    for (const entry of entries) {
        const fullPath = path.join(dir, entry);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            processDir(fullPath);
        } else if (entry.endsWith('.blade.php')) {
            const relPath = path.relative(viewsDir, fullPath).replace(/\\/g, '/');
            if (skipFiles.includes(relPath)) {
                console.log('SKIP:', relPath);
                continue;
            }
            let content = fs.readFileSync(fullPath, 'utf8');
            let original = content;
            for (const [search, replace] of replacements) {
                content = content.split(search).join(replace);
            }
            if (content !== original) {
                fs.writeFileSync(fullPath, content, 'utf8');
                console.log('UPDATED:', relPath);
            } else {
                console.log('no-change:', relPath);
            }
        }
    }
}

processDir(viewsDir);
console.log('Done!');
