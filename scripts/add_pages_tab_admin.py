# -*- coding: utf-8 -*-
path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\admin\admin_header.php'
with open(path, 'r', encoding='utf-8') as f:
    c = f.read()

target = """        <a href="/admin/blogs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'blogs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-newspaper"></i> Blogs CMS
        </a>"""

replacement = """        <a href="/admin/blogs.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'blogs.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-newspaper"></i> Blogs CMS
        </a>

        <a href="/admin/pages.php"
          class="px-3 py-2 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 <?= $current_page === 'pages.php' ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:text-white hover:bg-indigo-800' ?>">
          <i class="fa-solid fa-file-lines text-amber-300"></i> Policy & CMS Pages
        </a>"""

if target in c:
    c = c.replace(target, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(c)
    print("Added Policy & CMS Pages tab to admin_header.php")
else:
    print("Target already modified or not found")
