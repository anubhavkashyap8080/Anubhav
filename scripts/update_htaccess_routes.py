# -*- coding: utf-8 -*-
path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\.htaccess'
with open(path, 'r', encoding='utf-8') as f:
    c = f.read()

target = "RewriteRule ^robots\\.txt$ robots.php [L]"
replacement = """RewriteRule ^robots\\.txt$ robots.php [L]

    # Friendly Policy and CMS URLs
    RewriteRule ^about/?$ about.php [L,QSA]
    RewriteRule ^contact/?$ contact.php [L,QSA]
    RewriteRule ^privacy-policy/?$ privacy-policy.php [L,QSA]
    RewriteRule ^terms-conditions/?$ terms-conditions.php [L,QSA]
    RewriteRule ^shipping-policy/?$ shipping-policy.php [L,QSA]
    RewriteRule ^terms-of-supply/?$ terms-of-supply.php [L,QSA]
    RewriteRule ^page/([a-zA-Z0-9_-]+)/?$ page.php?slug=$1 [L,QSA]"""

if target in c and "RewriteRule ^privacy-policy" not in c:
    c = c.replace(target, replacement)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(c)
    print("Added friendly policy rewrite rules to .htaccess")
else:
    print("Already added or target not found")
