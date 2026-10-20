import React, { createContext, useContext, useState, ReactNode } from 'react';
import { HeaderConfig, FooterConfig, SEOConfig, SecurityChecklist } from '../types';
import {
  initialHeaderConfig,
  initialFooterConfig,
  initialSEOConfig,
  initialSecurityChecklist,
} from '../data/mockConfig';

interface SiteConfigContextType {
  headerConfig: HeaderConfig;
  setHeaderConfig: React.Dispatch<React.SetStateAction<HeaderConfig>>;
  footerConfig: FooterConfig;
  setFooterConfig: React.Dispatch<React.SetStateAction<FooterConfig>>;
  seoConfig: SEOConfig;
  setSeoConfig: React.Dispatch<React.SetStateAction<SEOConfig>>;
  securityChecklist: SecurityChecklist;
  setSecurityChecklist: React.Dispatch<React.SetStateAction<SecurityChecklist>>;
  resetConfigs: () => void;
}

const SiteConfigContext = createContext<SiteConfigContextType | undefined>(undefined);

export const SiteConfigProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [headerConfig, setHeaderConfig] = useState<HeaderConfig>(initialHeaderConfig);
  const [footerConfig, setFooterConfig] = useState<FooterConfig>(initialFooterConfig);
  const [seoConfig, setSeoConfig] = useState<SEOConfig>(initialSEOConfig);
  const [securityChecklist, setSecurityChecklist] = useState<SecurityChecklist>(initialSecurityChecklist);

  const resetConfigs = () => {
    setHeaderConfig(initialHeaderConfig);
    setFooterConfig(initialFooterConfig);
    setSeoConfig(initialSEOConfig);
    setSecurityChecklist(initialSecurityChecklist);
  };

  return (
    <SiteConfigContext.Provider
      value={{
        headerConfig,
        setHeaderConfig,
        footerConfig,
        setFooterConfig,
        seoConfig,
        setSeoConfig,
        securityChecklist,
        setSecurityChecklist,
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
