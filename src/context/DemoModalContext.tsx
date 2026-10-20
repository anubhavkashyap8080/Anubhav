import React, { createContext, useContext, useState, useCallback, ReactNode, useEffect } from 'react';

interface DemoModalContextType {
  isOpen: boolean;
  title: string;
  message: string;
  actionName?: string;
  showDemoNotice: (actionName?: string, customMessage?: string, customTitle?: string) => void;
  closeDemoNotice: () => void;
}

const DEFAULT_TITLE = "This is only a demo";
const DEFAULT_MESSAGE = "This feature is currently available for preview only. Backend functionality will be implemented in a future phase.";

const DemoModalContext = createContext<DemoModalContextType | undefined>(undefined);

export const DemoModalProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [title, setTitle] = useState(DEFAULT_TITLE);
  const [message, setMessage] = useState(DEFAULT_MESSAGE);
  const [actionName, setActionName] = useState<string | undefined>(undefined);

  const showDemoNotice = useCallback((action?: string, customMsg?: string, customTtl?: string) => {
    setActionName(action);
    setTitle(customTtl || DEFAULT_TITLE);
    setMessage(customMsg || DEFAULT_MESSAGE);
    setIsOpen(true);
  }, []);

  const closeDemoNotice = useCallback(() => {
    setIsOpen(false);
  }, []);

  // Handle ESC key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && isOpen) {
        closeDemoNotice();
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, closeDemoNotice]);

  return (
    <DemoModalContext.Provider
      value={{
        isOpen,
        title,
        message,
        actionName,
        showDemoNotice,
        closeDemoNotice,
      }}
    >
      {children}
    </DemoModalContext.Provider>
  );
};

export const useDemoModal = (): DemoModalContextType => {
  const context = useContext(DemoModalContext);
  if (!context) {
    throw new Error('useDemoModal must be used within a DemoModalProvider');
  }
  return context;
};

// Also export as useDemoAction for convenience
export const useDemoAction = useDemoModal;
