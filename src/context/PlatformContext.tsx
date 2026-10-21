import React, { createContext, useContext, useState, useEffect } from 'react';
import {
  User,
  UserRole,
  Seller,
  SupportTicket,
  TicketMessage,
  BlogPost,
  SEOConfig,
  MapSettings,
  EmailCampaign,
  SmsMessage,
  Product,
  Category
} from '../types';
import {
  initialUsers,
  initialTickets,
  initialSEOConfig,
  initialMapSettings,
  initialEmailCampaigns,
  initialSmsMessages
} from '../data/mockConfig';
import { mockSellers } from '../data/mockSellers';
import { mockBlogPosts } from '../data/mockBlogPosts';
import { mockProducts } from '../data/mockProducts';
import { mockCategories } from '../data/mockCategories';

import { SEED_CREDENTIALS } from '../utils/cryptoUtils';
import { pushRemoteUsers, syncOnStartup } from '../utils/cloudSync';

interface PlatformContextType {
  // Users Manager
  users: User[];
  addUser: (user: Omit<User, 'id' | 'joinedDate'>) => void;
  updateUserRole: (userId: string, newRole: UserRole) => void;
  updateUserPassword: (userId: string, passwordHash: string, passwordSalt: string) => void;
  toggleUserStatus: (userId: string) => void;
  deleteUser: (userId: string) => void;
  syncWithCloud: () => Promise<{ synced: boolean; count: number }>;

  // Vendors Manager
  vendors: Seller[];
  updateVendorKyc: (vendorId: string, status: 'Verified' | 'Pending Review' | 'Rejected') => void;
  updateVendorCommission: (vendorId: string, commission: number) => void;
  addVendor: (vendor: Omit<Seller, 'id'>) => void;

  // Tickets Manager
  tickets: SupportTicket[];
  createTicket: (data: {
    userName: string;
    userEmail: string;
    userPhone: string;
    subject: string;
    department: SupportTicket['department'];
    priority: SupportTicket['priority'];
    message: string;
  }) => SupportTicket;
  replyToTicket: (ticketId: string, messageText: string, senderName: string, senderRole: UserRole) => void;
  updateTicketStatus: (ticketId: string, status: SupportTicket['status']) => void;
  assignTicket: (ticketId: string, agentName: string) => void;

  // Blogs Manager
  posts: BlogPost[];
  createPost: (post: Omit<BlogPost, 'id' | 'publishedAt'>) => void;
  updatePost: (id: string, post: Partial<BlogPost>) => void;
  deletePost: (id: string) => void;

  // SEO & Technical Config
  seoConfig: SEOConfig;
  updateSeoConfig: (config: Partial<SEOConfig>) => void;
  generateSitemapXml: () => string;
  generateRobotsTxt: () => string;

  // Communication (Bulk Email & SMS)
  emailCampaigns: EmailCampaign[];
  sendEmailCampaign: (campaign: Omit<EmailCampaign, 'id' | 'sentDate' | 'status' | 'openRate'>) => void;
  smsMessages: SmsMessage[];
  sendSmsMessage: (sms: Omit<SmsMessage, 'id' | 'sentDate' | 'status'>) => void;

  // Products Manager
  products: Product[];
  addProduct: (product: Omit<Product, 'id'>) => void;
  updateProduct: (productId: string, updates: Partial<Product>) => void;
  deleteProduct: (productId: string) => void;

  // Categories Manager
  categories: Category[];
  addCategory: (category: Omit<Category, 'id'>) => void;
  updateCategory: (categoryId: string, updates: Partial<Category>) => void;
  deleteCategory: (categoryId: string) => void;

  // Map & Location Settings
  mapSettings: MapSettings;
  updateMapSettings: (settings: Partial<MapSettings>) => void;
}

const PlatformContext = createContext<PlatformContextType | undefined>(undefined);

export const PlatformProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // 1. Users State
  const [users, setUsers] = useState<User[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_platform_users');
      if (saved) {
        const parsed: User[] = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) {
          return parsed.map(u => {
            if (!u.passwordHash || !u.passwordSalt) {
              const seed = u.role === 'customer'
                ? SEED_CREDENTIALS.shopper
                : u.role === 'vendor'
                ? SEED_CREDENTIALS.seller
                : SEED_CREDENTIALS.admin;
              const sanitized = { ...u, passwordHash: seed.hashHex, passwordSalt: seed.saltHex };
              delete sanitized.password;
              return sanitized;
            }
            const sanitized = { ...u };
            delete sanitized.password;
            return sanitized;
          });
        }
      }
    } catch (e) {
      console.warn('Failed to load users:', e);
    }
    return initialUsers;
  });

  // 2. Vendors State
  const [vendors, setVendors] = useState<Seller[]>(() => {
    const saved = localStorage.getItem('ggh_platform_vendors');
    return saved ? JSON.parse(saved) : mockSellers;
  });

  // 3. Tickets State
  const [tickets, setTickets] = useState<SupportTicket[]>(() => {
    const saved = localStorage.getItem('ggh_platform_tickets');
    return saved ? JSON.parse(saved) : initialTickets;
  });

  // 4. Blogs State
  const [posts, setPosts] = useState<BlogPost[]>(() => {
    const saved = localStorage.getItem('ggh_platform_posts');
    return saved ? JSON.parse(saved) : mockBlogPosts;
  });

  // 5. SEO Config State
  const [seoConfig, setSeoConfig] = useState<SEOConfig>(() => {
    const saved = localStorage.getItem('ggh_platform_seo');
    return saved ? JSON.parse(saved) : initialSEOConfig;
  });

  // 6. Communications State
  const [emailCampaigns, setEmailCampaigns] = useState<EmailCampaign[]>(() => {
    const saved = localStorage.getItem('ggh_platform_emails');
    return saved ? JSON.parse(saved) : initialEmailCampaigns;
  });

  const [smsMessages, setSmsMessages] = useState<SmsMessage[]>(() => {
    const saved = localStorage.getItem('ggh_platform_sms');
    return saved ? JSON.parse(saved) : initialSmsMessages;
  });

  // 7. Map Settings
  const [mapSettings, setMapSettings] = useState<MapSettings>(() => {
    const saved = localStorage.getItem('ggh_platform_map');
    return saved ? JSON.parse(saved) : initialMapSettings;
  });

  // 8. Products State
  const [products, setProducts] = useState<Product[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_platform_products');
      return saved ? JSON.parse(saved) : mockProducts;
    } catch {
      return mockProducts;
    }
  });

  // 9. Categories State
  const [categories, setCategories] = useState<Category[]>(() => {
    try {
      const saved = localStorage.getItem('ggh_platform_categories');
      return saved ? JSON.parse(saved) : mockCategories;
    } catch {
      return mockCategories;
    }
  });

  // Persist states & propagate to central sync server
  useEffect(() => {
    localStorage.setItem('ggh_platform_users', JSON.stringify(users));
    pushRemoteUsers(users).catch(() => {});
  }, [users]);

  // Synchronize users state when an external sync or pairing event fires
  useEffect(() => {
    const handleSync = () => {
      const saved = localStorage.getItem('ggh_platform_users');
      if (saved) {
        try {
          const parsed = JSON.parse(saved);
          if (Array.isArray(parsed) && parsed.length > 0) {
            setUsers(parsed);
          }
        } catch {}
      }
    };
    window.addEventListener('ggh_users_synced', handleSync);
    return () => window.removeEventListener('ggh_users_synced', handleSync);
  }, []);

  const syncWithCloud = async () => {
    const res = await syncOnStartup();
    const saved = localStorage.getItem('ggh_platform_users');
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        if (Array.isArray(parsed)) setUsers(parsed);
      } catch {}
    }
    return { synced: res.synced, count: res.count };
  };

  useEffect(() => { localStorage.setItem('ggh_platform_vendors', JSON.stringify(vendors)); }, [vendors]);
  useEffect(() => { localStorage.setItem('ggh_platform_tickets', JSON.stringify(tickets)); }, [tickets]);
  useEffect(() => { localStorage.setItem('ggh_platform_posts', JSON.stringify(posts)); }, [posts]);
  useEffect(() => { localStorage.setItem('ggh_platform_seo', JSON.stringify(seoConfig)); }, [seoConfig]);
  useEffect(() => { localStorage.setItem('ggh_platform_emails', JSON.stringify(emailCampaigns)); }, [emailCampaigns]);
  useEffect(() => { localStorage.setItem('ggh_platform_sms', JSON.stringify(smsMessages)); }, [smsMessages]);
  useEffect(() => { localStorage.setItem('ggh_platform_map', JSON.stringify(mapSettings)); }, [mapSettings]);
  useEffect(() => { localStorage.setItem('ggh_platform_products', JSON.stringify(products)); }, [products]);
  useEffect(() => { localStorage.setItem('ggh_platform_categories', JSON.stringify(categories)); }, [categories]);

  // User Actions
  const addUser = (userData: Omit<User, 'id' | 'joinedDate'>) => {
    const newUser: User = {
      ...userData,
      id: `user-${Date.now()}`,
      joinedDate: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    };
    setUsers(prev => [newUser, ...prev]);
  };

  const updateUserRole = (userId: string, newRole: UserRole) => {
    setUsers(prev => prev.map(u => u.id === userId ? { ...u, role: newRole } : u));
  };

  const updateUserPassword = (userId: string, passwordHash: string, passwordSalt: string) => {
    setUsers(prev => prev.map(u => {
      if (u.id === userId) {
        const updated = { ...u, passwordHash, passwordSalt };
        delete updated.password;
        return updated;
      }
      return u;
    }));
  };

  const toggleUserStatus = (userId: string) => {
    setUsers(prev => prev.map(u => u.id === userId ? { ...u, status: u.status === 'active' ? 'suspended' : 'active' } : u));
  };

  const deleteUser = (userId: string) => {
    setUsers(prev => prev.filter(u => u.id !== userId));
  };

  // Vendor Actions
  const updateVendorKyc = (vendorId: string, status: 'Verified' | 'Pending Review' | 'Rejected') => {
    setVendors(prev => prev.map(v => v.id === vendorId ? { ...v, kycStatus: status, verified: status === 'Verified' } : v));
  };

  const updateVendorCommission = (vendorId: string, commission: number) => {
    setVendors(prev => prev.map(v => v.id === vendorId ? { ...v, commissionRate: commission } : v));
  };

  const addVendor = (vendorData: Omit<Seller, 'id'>) => {
    const newVendor: Seller = {
      ...vendorData,
      id: `seller-${Date.now()}`
    };
    setVendors(prev => [newVendor, ...prev]);
  };

  // Ticket Actions
  const createTicket = (data: {
    userName: string;
    userEmail: string;
    userPhone: string;
    subject: string;
    department: SupportTicket['department'];
    priority: SupportTicket['priority'];
    message: string;
  }) => {
    const newTicket: SupportTicket = {
      id: `tkt-${Date.now()}`,
      ticketNumber: `GGH-2026-${Math.floor(100 + Math.random() * 900)}`,
      userId: `user-guest-${Date.now()}`,
      userName: data.userName,
      userEmail: data.userEmail,
      userPhone: data.userPhone,
      subject: data.subject,
      department: data.department,
      priority: data.priority,
      status: 'Open',
      createdAt: 'Just now',
      updatedAt: 'Just now',
      assignedAgent: 'Support Team',
      messages: [
        {
          id: `msg-${Date.now()}`,
          senderId: 'client',
          senderName: data.userName,
          senderRole: 'customer',
          message: data.message,
          timestamp: 'Just now'
        }
      ]
    };
    setTickets(prev => [newTicket, ...prev]);
    return newTicket;
  };

  const replyToTicket = (ticketId: string, messageText: string, senderName: string, senderRole: UserRole) => {
    const newMessage: TicketMessage = {
      id: `msg-${Date.now()}`,
      senderId: 'agent',
      senderName,
      senderRole,
      message: messageText,
      timestamp: 'Just now'
    };
    setTickets(prev => prev.map(t => {
      if (t.id === ticketId) {
        return {
          ...t,
          updatedAt: 'Just now',
          status: t.status === 'Open' ? 'In Progress' : t.status,
          messages: [...t.messages, newMessage]
        };
      }
      return t;
    }));
  };

  const updateTicketStatus = (ticketId: string, status: SupportTicket['status']) => {
    setTickets(prev => prev.map(t => t.id === ticketId ? { ...t, status, updatedAt: 'Just now' } : t));
  };

  const assignTicket = (ticketId: string, agentName: string) => {
    setTickets(prev => prev.map(t => t.id === ticketId ? { ...t, assignedAgent: agentName, updatedAt: 'Just now' } : t));
  };

  // Blog Actions
  const createPost = (postData: Omit<BlogPost, 'id' | 'publishedAt'>) => {
    const newPost: BlogPost = {
      ...postData,
      id: `post-${Date.now()}`,
      publishedAt: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    };
    setPosts(prev => [newPost, ...prev]);
  };

  const updatePost = (id: string, postData: Partial<BlogPost>) => {
    setPosts(prev => prev.map(p => p.id === id ? { ...p, ...postData } : p));
  };

  const deletePost = (id: string) => {
    setPosts(prev => prev.filter(p => p.id !== id));
  };

  // SEO & Technical Config
  const updateSeoConfig = (config: Partial<SEOConfig>) => {
    setSeoConfig(prev => ({ ...prev, ...config }));
  };

  const generateSitemapXml = () => {
    const baseUrl = seoConfig.canonicalUrl || 'https://ganvgalihat.pages.dev';
    const staticRoutes = [
      { loc: `${baseUrl}/`, priority: '1.0', changefreq: 'daily' },
      { loc: `${baseUrl}/products`, priority: '0.9', changefreq: 'daily' },
      { loc: `${baseUrl}/nearby-artisans`, priority: '0.8', changefreq: 'daily' },
      { loc: `${baseUrl}/about`, priority: '0.7', changefreq: 'weekly' },
      { loc: `${baseUrl}/contact`, priority: '0.7', changefreq: 'monthly' },
      { loc: `${baseUrl}/blog`, priority: '0.8', changefreq: 'weekly' }
    ];

    const productRoutes = mockProducts.map(p => ({
      loc: `${baseUrl}/product/${p.slug}`,
      priority: '0.8',
      changefreq: 'weekly'
    }));

    const blogRoutes = posts.filter(b => b.status === 'Published').map(b => ({
      loc: `${baseUrl}/blog/${b.slug}`,
      priority: '0.7',
      changefreq: 'monthly'
    }));

    const allUrls = [...staticRoutes, ...productRoutes, ...blogRoutes];

    return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${allUrls.map(u => `  <url>
    <loc>${u.loc}</loc>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
    <changefreq>${u.changefreq}</changefreq>
    <priority>${u.priority}</priority>
  </url>`).join('\n')}
</urlset>`;
  };

  const generateRobotsTxt = () => {
    return seoConfig.robotsTxt;
  };

  // Communication Actions
  const sendEmailCampaign = (campaignData: Omit<EmailCampaign, 'id' | 'sentDate' | 'status' | 'openRate'>) => {
    const newCamp: EmailCampaign = {
      ...campaignData,
      id: `camp-${Date.now()}`,
      sentDate: 'Just now',
      status: 'Sent',
      openRate: '0.0%'
    };
    setEmailCampaigns(prev => [newCamp, ...prev]);
  };

  const sendSmsMessage = (smsData: Omit<SmsMessage, 'id' | 'sentDate' | 'status'>) => {
    const newSms: SmsMessage = {
      ...smsData,
      id: `sms-${Date.now()}`,
      sentDate: 'Just now',
      status: 'Delivered'
    };
    setSmsMessages(prev => [newSms, ...prev]);
  };

  // Map Actions
  const updateMapSettings = (settings: Partial<MapSettings>) => {
    setMapSettings(prev => ({ ...prev, ...settings }));
  };

  // Product Actions
  const addProduct = (prodData: Omit<Product, 'id'>) => {
    const newProduct: Product = {
      ...prodData,
      id: `prod-${Date.now()}`,
      code: prodData.code || `GGH-${Date.now().toString().slice(-4)}`
    };
    setProducts(prev => [newProduct, ...prev]);
  };

  const updateProduct = (productId: string, updates: Partial<Product>) => {
    setProducts(prev => prev.map(p => p.id === productId ? { ...p, ...updates } : p));
  };

  const deleteProduct = (productId: string) => {
    setProducts(prev => prev.filter(p => p.id !== productId));
  };

  // Category Actions
  const addCategory = (catData: Omit<Category, 'id'>) => {
    const newCat: Category = {
      ...catData,
      id: `cat-${catData.slug || Date.now()}`,
      status: catData.status || 'active'
    };
    setCategories(prev => [...prev, newCat]);
  };

  const updateCategory = (catId: string, updates: Partial<Category>) => {
    setCategories(prev => prev.map(c => c.id === catId ? { ...c, ...updates } : c));
  };

  const deleteCategory = (catId: string) => {
    setCategories(prev => prev.filter(c => c.id !== catId));
  };

  return (
    <PlatformContext.Provider
      value={{
        users,
        addUser,
        updateUserRole,
        updateUserPassword,
        toggleUserStatus,
        deleteUser,
        syncWithCloud,
        vendors,
        updateVendorKyc,
        updateVendorCommission,
        addVendor,
        tickets,
        createTicket,
        replyToTicket,
        updateTicketStatus,
        assignTicket,
        posts,
        createPost,
        updatePost,
        deletePost,
        seoConfig,
        updateSeoConfig,
        generateSitemapXml,
        generateRobotsTxt,
        emailCampaigns,
        sendEmailCampaign,
        smsMessages,
        sendSmsMessage,
        mapSettings,
        updateMapSettings,
        products,
        addProduct,
        updateProduct,
        deleteProduct,
        categories,
        addCategory,
        updateCategory,
        deleteCategory
      }}
    >
      {children}
    </PlatformContext.Provider>
  );
};

export const usePlatform = () => {
  const context = useContext(PlatformContext);
  if (!context) {
    throw new Error('usePlatform must be used within a PlatformProvider');
  }
  return context;
};
