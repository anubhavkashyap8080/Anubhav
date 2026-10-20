import React, { useEffect, useRef } from 'react';
import { useDemoModal } from '../../context/DemoModalContext';
import { X, Sparkles, AlertCircle } from 'lucide-react';

export const DemoModal: React.FC = () => {
  const { isOpen, title, message, actionName, closeDemoNotice } = useDemoModal();
  const primaryButtonRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (isOpen) {
      // Focus primary button when modal opens
      setTimeout(() => {
        primaryButtonRef.current?.focus();
      }, 50);
      // Prevent background scrolling
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity"
      role="dialog"
      aria-modal="true"
      aria-labelledby="demo-modal-title"
      aria-describedby="demo-modal-desc"
      onClick={closeDemoNotice}
    >
      <div
        className="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-amber-100 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Top banner accent */}
        <div className="h-2 bg-gradient-to-r from-brand-green via-brand-orange to-brand-green" />

        <div className="p-6">
          {/* Close button */}
          <button
            onClick={closeDemoNotice}
            className="absolute top-4 right-4 p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-brand-green"
            aria-label="Close dialog"
          >
            <X className="w-5 h-5" />
          </button>

          {/* Icon badge */}
          <div className="w-12 h-12 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-brand-orange mb-4">
            <Sparkles className="w-6 h-6 text-brand-orange" />
          </div>

          {actionName && (
            <div className="inline-flex items-center gap-1.5 px-2.5 py-1 mb-2.5 rounded-full bg-gray-100 text-xs font-medium text-gray-700">
              <AlertCircle className="w-3.5 h-3.5 text-brand-green" />
              <span>Action: {actionName}</span>
            </div>
          )}

          {/* Title */}
          <h3
            id="demo-modal-title"
            className="text-xl font-bold text-gray-900 mb-2 font-display"
          >
            {title}
          </h3>

          {/* Message */}
          <p
            id="demo-modal-desc"
            className="text-sm text-gray-600 leading-relaxed mb-6"
          >
            {message}
          </p>

          {/* Footer Action */}
          <div className="flex justify-end gap-3 pt-2">
            <button
              ref={primaryButtonRef}
              type="button"
              onClick={closeDemoNotice}
              className="w-full sm:w-auto px-6 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white font-medium text-sm rounded-lg shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 active:scale-95"
            >
              Got it
            </button>
          </div>
        </div>

        {/* Subtle footer notice */}
        <div className="px-6 py-2.5 bg-brand-sand/60 border-t border-gray-100 text-[11px] text-gray-500 text-center">
          GanvGaliHut Frontend Interactive Prototype • Phase 1
        </div>
      </div>
    </div>
  );
};
