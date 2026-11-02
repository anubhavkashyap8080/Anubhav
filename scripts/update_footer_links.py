# -*- coding: utf-8 -*-
path = r'C:\Users\HP\.gemini\antigravity\scratch\ganvgalihat\includes\footer.php'
with open(path, 'r', encoding='utf-8') as f:
    c = f.read()

# Replace fallbacks in footer
c = c.replace(
    "get_setting('privacy_policy_url', '/about.php')",
    "get_setting('privacy_policy_url', '/privacy-policy.php')"
)
c = c.replace(
    "get_setting('shipping_policy_url', '/about.php')",
    "get_setting('shipping_policy_url', '/shipping-policy.php')"
)
c = c.replace(
    """<a href="<?= htmlspecialchars(get_setting('terms_conditions_url', '/about.php')) ?>" class="hover:text-slate-400">Terms of Supply</a>""",
    """<a href="<?= htmlspecialchars(get_setting('terms_conditions_url', '/terms-conditions.php')) ?>" class="hover:text-slate-300">Terms & Conditions</a>
          <span>•</span>
          <a href="<?= htmlspecialchars(get_setting('terms_of_supply_url', '/terms-of-supply.php')) ?>" class="hover:text-slate-300">Terms of Supply</a>"""
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(c)

print("Updated footer links in includes/footer.php successfully!")
