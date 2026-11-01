# -*- coding: utf-8 -*-
"""
GanvGaliHat v2 Production ZIP Packager for cPanel Deployment
- Preserves the existing gaungalihat_cpanel_production.zip intact
- Packages the complete hardened, upgraded codebase into ganvgalihat_cpanel_v2_production.zip
- Ensures proper UNIX forward slashes in archive paths
"""
import os
import zipfile

source_dir = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat'
dest_zips = [
    r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat_cpanel_v2_production.zip',
    r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\ganvgalihat_cpanel_v2_production.zip'
]

exclude_dirs = {
    'node_modules', '.git', '.wrangler', 'tests', 'dist', 'backups'
}

exclude_files = {
    '.gitignore', 'package.json', 'package-lock.json', 'tsconfig.json',
    'vite.config.ts', 'wrangler.toml', 'postcss.config.js', 'tailwind.config.js',
    'README.md', 'gaungalihat_cpanel_production.zip', 'ganvgalihat_cpanel_v2_production.zip',
    'config.local.php'
}

exclude_exts = {'.zip', '.log', '.tmp', '.sqlite'}

file_count = 0
total_size = 0

for dest_zip in dest_zips:
    with zipfile.ZipFile(dest_zip, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(source_dir):
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            
            for file in files:
                if file in exclude_files:
                    continue
                ext = os.path.splitext(file)[1].lower()
                if ext in exclude_exts:
                    continue
                
                full_path = os.path.join(root, file)
                rel_path = os.path.relpath(full_path, source_dir)
                
                # Use standard UNIX forward slashes in zip entries
                arc_name = rel_path.replace('\\', '/')
                
                zipf.write(full_path, arc_name)
                if dest_zip == dest_zips[0]:
                    file_count += 1
                    total_size += os.path.getsize(full_path)

print(f"Packed {file_count} files ({total_size / (1024*1024):.2f} MB uncompressed) into production ZIP.")
for dz in dest_zips:
    print(f"Created: {dz} ({os.path.getsize(dz) / (1024*1024):.2f} MB)")
