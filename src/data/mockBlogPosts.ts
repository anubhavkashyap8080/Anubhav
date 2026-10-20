import { BlogPost } from '../types';

export const mockBlogCategories = [
  'All',
  'Farming & Traditional Tools',
  'Rural Engineering & Industry',
  'Mahila Udyamita',
  'Handmade Products',
  'Village Lifestyle',
  'Eco Materials & Jute'
];

export const mockBlogPosts: BlogPost[] = [
  {
    id: 'post-1',
    title: 'The Secret Tempering of Hardoi Steel: How S S Industries Keeps Farming Tools Razor Sharp for Decades',
    slug: 'secret-tempering-hardoi-steel-farming-tools',
    excerpt: 'Explore how reclaimed high-carbon automotive leaf springs undergo coal-fired forging and oil quenching at S S Industries in Hardoi to create sickles, khurpis, and spades that outlast commercial stamped tools.',
    content: `
      <h2>The Living Science of Village Blacksmithing</h2>
      <p>In modern agricultural hardware markets, cheap mass-produced farming implements are stamped from soft low-grade sheet metal. Within a few weeks of clearing rocky ground or cutting fibrous sugarcane, the edges roll, dent, and dull permanently.</p>
      <p>At S S Industries Works in Hardoi (Madhoganj Baghauli Road), blacksmiths and toolmakers use a vastly superior metallurgical practice: transforming reclaimed high-carbon automotive leaf springs through controlled coal-hearth forging.</p>
      
      <h3>The 5 Steps of S S Industries Tool Forging:</h3>
      <ul>
        <li><strong>Step 1 - High-Carbon Spring Steel Selection:</strong> Sourcing high-tensile EN-45 and 5160 grade spring steel known for exceptional elasticity and wear resistance.</li>
        <li><strong>Step 2 - Forge Heating to 1,050°C:</strong> Heating the billet in clean charcoal hearths until it glows bright cherry-red.</li>
        <li><strong>Step 3 - Power & Hand Hammer Tapering:</strong> Drawing out the bevel gradually from 5mm spine down to 1mm cutting geometry.</li>
        <li><strong>Step 4 - Differential Oil Quenching:</strong> Dipping the cutting edge into proprietary vegetable quenching oils to lock in 54-56 HRC hardness while keeping the spine flexible.</li>
        <li><strong>Step 5 - Sheesham Handle Riveting:</strong> Securing seasoned hardwood handles with heavy solid iron rivets that never loosen.</li>
      </ul>

      <blockquote>"A genuine hand-forged Hansiya does not just cut grass; it maintains its bite through years of abrasive Indian soil contact."</blockquote>

      <h2>Direct from Hardoi to Bharat's Farmers</h2>
      <p>Through GanvGaliHut, S S Industries brings authentic agricultural implements and cottage workshop tools directly to farmers and gardening enthusiasts without middleman inflation.</p>
    `,
    category: 'Farming & Traditional Tools',
    categorySlug: 'farming-traditional-tools',
    image: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Anubhav Kashyap',
      role: 'Production Engineer, S S Industries',
      avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 16, 2026',
    readTime: '6 min read',
    featured: true,
    tags: ['S S Industries', 'Hardoi', 'Hansiya', 'Khurpi', 'Forged Steel', 'Farm Tools'],
    status: 'Published',
    seo: {
      title: 'The Secret Tempering of Hardoi Steel | S S Industries Farm Tools',
      metaDescription: 'Discover why S S Industries forged carbon steel sickles and khurpis from Hardoi outlast mass-produced garden tools.',
      canonicalUrl: 'https://ganvgalihat.com/blog/secret-tempering-hardoi-steel-farming-tools',
      ogImage: 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-2',
    title: 'Golden Fiber Revival: How Rural Women SHGs Are Replacing 100,000 Plastic Bags with Biodegradable Jute',
    slug: 'golden-fiber-revival-rural-women-jute-bags',
    excerpt: 'From village cluster stitching units in Hardoi to metro supermarkets, discover how rural women self-help collectives are leading India\'s zero-waste movement with heavy-duty golden jute and cloth jholas.',
    content: `
      <h2>The Miracle of the Golden Fiber</h2>
      <p>Single-use plastic carry bags take 400 years to decompose, choking city drainage and poisoning cattle in rural pastures. In response, women self-help groups across Awadh have embraced golden jute—India's indigenous miracle fiber.</p>
      <p>Jute consumes negligible chemical fertilizers, absorbs carbon dioxide faster than most rainforest trees, and produces a tensile fiber strong enough to haul 20 kg of groceries without tearing.</p>

      <h3>How Awadh Mahila SHG Crafts Each Tote:</h3>
      <ul>
        <li><strong>Fine Weave Selection:</strong> Specially treated 14x15 mesh count natural golden jute fabric with zero synthetic laminates.</li>
        <li><strong>Double-Lock Stitching:</strong> High-tensile nylon-bonded thread stitched with reinforced cross-box corners.</li>
        <li><strong>Comfort Padded Handles:</strong> Cotton webbing handles that do not pinch fingers even when carrying heavy melons or flour.</li>
      </ul>

      <blockquote>"Every bag stitched puts ₹40 to ₹60 of direct, unmediated income into the savings account of a rural mother."</blockquote>
    `,
    category: 'Eco Materials & Jute',
    categorySlug: 'eco-materials-jute',
    image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Kusum Devi',
      role: 'SHG Cluster President, Mallawan',
      avatar: 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 14, 2026',
    readTime: '5 min read',
    featured: false,
    tags: ['Jute Bags', 'Zero Waste', 'Women SHG', 'Plastic Free', 'Rural Crafts'],
    status: 'Published',
    seo: {
      title: 'Golden Fiber Revival: Rural Women SHG Jute Bags | GanvGaliHut',
      metaDescription: 'Learn how rural women self-help groups are crafting biodegradable heavy-duty jute shopping totes that eliminate single-use plastics.',
      canonicalUrl: 'https://ganvgalihat.com/blog/golden-fiber-revival-rural-women-jute-bags',
      ogImage: 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  },
  {
    id: 'post-3',
    title: 'The 200-Year Legacy of Channapatna: Why Natural Lacquer Wooden Toys Are Safe for Your Children',
    slug: '200-year-legacy-channapatna-wooden-toys',
    excerpt: 'Step inside the workshops of Ramanagara, where GI-certified artisans turn ivory wood and brush it with vegetable dyes like turmeric and indigo to make toys that children can safely chew and cherish.',
    content: `
      <h2>Toys Born from Trees, Colored by Nature</h2>
      <p>Modern plastic toys leach phthalates, heavy metals, and microplastics into children’s hands and mouths. Two centuries ago, artisans in Karnataka perfected a child-safe alternative: Channapatna Lacquerware.</p>
      <p>Using soft Wrightia Tinctoria wood, artisans turn smooth beads, stackers, and animal toys on spinning lathes. While the piece rotates, they press colored lac sticks made from purified tree resin blended with food-grade turmeric, kumkum, and indigo.</p>
      <blockquote>"No paintbrushes or synthetic lacquers are ever used. The friction of the spinning wood melts the organic resin directly into the grain."</blockquote>
    `,
    category: 'Village Lifestyle',
    categorySlug: 'village-lifestyle',
    image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
    author: {
      name: 'Nagaraju Gowda',
      role: 'Master Artisan, Channapatna Guild',
      avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80'
    },
    publishedAt: 'October 11, 2026',
    readTime: '4 min read',
    featured: false,
    tags: ['Channapatna', 'Wooden Toys', 'Child Safe', 'Handicrafts', 'GI Tag'],
    status: 'Published',
    seo: {
      title: 'The 200-Year Legacy of Channapatna Wooden Toys | GanvGaliHut',
      metaDescription: 'Discover why GI Channapatna natural wooden toys colored with vegetable dyes are the best non-toxic playthings for toddlers.',
      canonicalUrl: 'https://ganvgalihat.com/blog/200-year-legacy-channapatna-wooden-toys',
      ogImage: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
      indexRobots: true
    }
  }
];
