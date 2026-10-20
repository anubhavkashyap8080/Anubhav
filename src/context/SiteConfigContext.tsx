import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { HeaderConfig, FooterConfig, SEOConfig, SecurityChecklist, SocialLinksConfig, HeroFestivalConfig } from '../types';
import {
  initialHeaderConfig,
  initialFooterConfig,
  initialSEOConfig,
  initialSecurityChecklist,
  initialHeroFestivalConfig,
  FESTIVAL_PRESETS
} from '../data/mockConfig';

interface SiteConfigContextType {
  headerConfig: HeaderConfig;
  setHeaderConfig: React.Dispatch<React.SetStateAction<HeaderConfig>>;
  updateHeaderConfig: (updates: Partial<HeaderConfig>) => void;
  footerConfig: FooterConfig;
  setFooterConfig: React.Dispatch<React.SetStateAction<FooterConfig>>;
  updateFooterConfig: (updates: Partial<FooterConfig>) => void;
  updateSocialLinks: (updates: Partial<SocialLinksConfig>) => void;
  seoConfig: SEOConfig;
  setSeoConfig: React.Dispatch<React.SetStateAction<SEOConfig>>;
  securityChecklist: SecurityChecklist;
  setSecurityChecklist: React.Dispatch<React.SetStateAction<SecurityChecklist>>;
  heroFestivalConfig: HeroFestivalConfig;
  setHeroFestivalConfig: React.Dispatch<React.SetStateAction<HeroFestivalConfig>>;
  updateHeroFestivalConfig: (updates: Partial<HeroFestivalConfig>) => void;
  applyFestivalPreset: (presetKey: string) => void;
  resetConfigs: () => void;
}

const SiteConfigContext = createContext<SiteConfigContextType | undefined>(undefined);

const HEADER_STORAGE = 'ggh_site_header_config';
const FOOTER_STORAGE = 'ggh_site_footer_config';
const SEO_STORAGE = 'ggh_site_seo_config';
const SEC_STORAGE = 'ggh_site_sec_config';
const HERO_FESTIVAL_STORAGE = 'ggh_hero_festival_config';

export const SiteConfigProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [headerConfig, setHeaderConfig] = useState<HeaderConfig>(() => {
    try {
      const saved = localStorage.getItem(HEADER_STORAGE);
      return saved ? JSON.parse(saved) : initialHeaderConfig;
    } catch {
      return initialHeaderConfig;
    }
  });

  const [footerConfig, setFooterConfig] = useState<FooterConfig>(() => {
    try {
      const saved = localStorage.getItem(FOOTER_STORAGE);
      return saved ? JSON.parse(saved) : initialFooterConfig;
    } catch {
      return initialFooterConfig;
    }
  });

  const [seoConfig, setSeoConfig] = useState<SEOConfig>(() => {
    try {
      const saved = localStorage.getItem(SEO_STORAGE);
      return saved ? JSON.parse(saved) : initialSEOConfig;
    } catch {
      return initialSEOConfig;
    }
  });

  const [securityChecklist, setSecurityChecklist] = useState<SecurityChecklist>(() => {
    try {
      const saved = localStorage.getItem(SEC_STORAGE);
      return saved ? JSON.parse(saved) : initialSecurityChecklist;
    } catch {
      return initialSecurityChecklist;
    }
  });

  const [heroFestivalConfig, setHeroFestivalConfig] = useState<HeroFestivalConfig>(() => {
    try {
      const saved = localStorage.getItem(HERO_FESTIVAL_STORAGE);
      return saved ? JSON.parse(saved) : initialHeroFestivalConfig;
    } catch {
      return initialHeroFestivalConfig;
    }
  });

  // Sync to local storage
  useEffect(() => { localStorage.setItem(HEADER_STORAGE, JSON.stringify(headerConfig)); }, [headerConfig]);
  useEffect(() => { localStorage.setItem(FOOTER_STORAGE, JSON.stringify(footerConfig)); }, [footerConfig]);
  useEffect(() => { localStorage.setItem(SEO_STORAGE, JSON.stringify(seoConfig)); }, [seoConfig]);
  useEffect(() => { localStorage.setItem(SEC_STORAGE, JSON.stringify(securityChecklist)); }, [securityChecklist]);
  useEffect(() => { localStorage.setItem(HERO_FESTIVAL_STORAGE, JSON.stringify(heroFestivalConfig)); }, [heroFestivalConfig]);

  const updateHeaderConfig = (updates: Partial<HeaderConfig>) => {
    setHeaderConfig(prev => ({ ...prev, ...updates }));
  };

  const updateFooterConfig = (updates: Partial<FooterConfig>) => {
    setFooterConfig(prev => ({ ...prev, ...updates }));
  };

  const updateSocialLinks = (updates: Partial<SocialLinksConfig>) => {
    setFooterConfig(prev => ({
      ...prev,
      socialLinks: {
        ...prev.socialLinks,
        ...updates
      }
    }));
  };

  const updateHeroFestivalConfig = (updates: Partial<HeroFestivalConfig>) => {
    setHeroFestivalConfig(prev => ({ ...prev, ...updates }));
  };

  const applyFestivalPreset = (presetKey: string) => {
    const preset = FESTIVAL_PRESETS[presetKey];
    if (preset) {
      setHeroFestivalConfig(prev => ({ ...prev, ...preset }));
    }
  };

  const resetConfigs = () => {
    setHeaderConfig(initialHeaderConfig);
    setFooterConfig(initialFooterConfig);
    setSeoConfig(initialSEOConfig);
    setSecurityChecklist(initialSecurityChecklist);
    setHeroFestivalConfig(initialHeroFestivalConfig);
    localStorage.removeItem(HEADER_STORAGE);
    localStorage.removeItem(FOOTER_STORAGE);
    localStorage.removeItem(SEO_STORAGE);
    localStorage.removeItem(SEC_STORAGE);
    localStorage.removeItem(HERO_FESTIVAL_STORAGE);
  };

  return (
    <SiteConfigContext.Provider
      value={{
        headerConfig,
        setHeaderConfig,
        updateHeaderConfig,
        footerConfig,
        setFooterConfig,
        updateFooterConfig,
        updateSocialLinks,
        seoConfig,
        setSeoConfig,
        securityChecklist,
        setSecurityChecklist,
        heroFestivalConfig,
        setHeroFestivalConfig,
        updateHeroFestivalConfig,
        applyFestivalPreset,
        resetConfigs,
      }}
    >
      {children}
    </SiteConfigContext.Provider>
  );
};

export const useSiteConfig = (): SiteConfigContextType => {
  const context = useContext(SiteConfigContext);
  if (!context) {
    throw new Error('useSiteConfig must be used within a SiteConfigProvider');
  }
  return context;
};
