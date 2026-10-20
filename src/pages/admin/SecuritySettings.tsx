import React from 'react';
import {
  ShieldCheck,
  Lock,
  KeyRound,
  FileCheck,
  AlertTriangle,
  Info,
  CheckCircle2,
  Server,
  Layers,
  FileCode,
  Save,
  RotateCcw,
  ExternalLink
} from 'lucide-react';
import { useDemoModal } from '../../context/DemoModalContext';
import { useSiteConfig } from '../../context/SiteConfigContext';

export const SecuritySettings: React.FC = () => {
  const { showDemoNotice } = useDemoModal();
  const { securityChecklist, setSecurityChecklist } = useSiteConfig();

  const handleAction = (actionName: string) => {
    // Section 10 requirement: All security configuration Save/Apply/Test buttons must display demo popup
    showDemoNotice(
      actionName,
      'This feature is currently available for preview only. Backend functionality, cryptographic key exchange, and server-side headers will be implemented and audited in a future phase.'
    );
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      
      {/* Top Banner Notice */}
      <div className="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 text-xs text-amber-900">
        <Info className="w-5 h-5 text-brand-orange shrink-0 mt-0.5" />
        <div>
          <span className="font-bold">Phase 1 Frontend Security Architecture Notice:</span> This section outlines production security requirements and best practices. A frontend-only prototype is not suitable for live transactions or sensitive financial data. Real authentication, HttpOnly cookies, and CSRF barriers will be implemented when the backend is introduced.
        </div>
      </div>

      {/* Header and Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 font-display">
            Security & Privacy Architecture Controls
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Production hardening requirements, HTTP security headers, and privacy governance checklists.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => handleAction('Run Pre-Deployment Security Audit')}
            className="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl flex items-center gap-1.5 cursor-pointer"
          >
            <ShieldCheck className="w-3.5 h-3.5 text-brand-green" />
            <span>Audit Architecture (Demo)</span>
          </button>

          <button
            onClick={() => handleAction('Apply Security Policy Profile')}
            className="px-5 py-2 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Save className="w-3.5 h-3.5" />
            <span>Apply Policies (Demo)</span>
          </button>
        </div>
      </div>

      {/* Grid of Security Checklists */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
        
        {/* 1. Transport & TLS Status */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <Lock className="w-4 h-4 text-emerald-600" />
              <span>1. TLS & Transport Security</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-3">
              Enforces modern HTTPS with TLS 1.3, automated certificate renewal via Cloudflare/Let's Encrypt, and HSTS preloading.
            </p>
            <div className="space-y-2">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={securityChecklist.httpsConfigured}
                  onChange={(e) => setSecurityChecklist({ ...securityChecklist, httpsConfigured: e.target.checked })}
                  className="rounded text-brand-green"
                />
                <span className="font-medium text-gray-800">Strict HTTPS / SSL Certificate (Demo Status)</span>
              </label>
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={securityChecklist.hstsEnabled}
                  onChange={(e) => setSecurityChecklist({ ...securityChecklist, hstsEnabled: e.target.checked })}
                  className="rounded text-brand-green"
                />
                <span className="font-medium text-gray-800">HTTP Strict Transport Security (HSTS 1 Year)</span>
              </label>
            </div>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Requires DNS SSL binding at edge deployment.
          </span>
        </div>

        {/* 2. Content Security Policy (CSP) */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <Layers className="w-4 h-4 text-blue-600" />
              <span>2. Content Security Policy (CSP)</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-3">
              Restricts executable script origins, disallows inline eval(), and blocks unauthorized cross-domain media exfiltration.
            </p>
            <div className="p-2.5 bg-gray-950 text-emerald-300 font-mono text-[10px] rounded-lg overflow-x-auto leading-normal">
              default-src 'self'; img-src 'self' data: https://images.unsplash.com; script-src 'self';
            </div>
          </div>
          <button
            onClick={() => handleAction('Configure CSP Directives')}
            className="text-xs font-semibold text-brand-green hover:underline text-left cursor-pointer"
          >
            Configure CSP Directives &rarr;
          </button>
        </div>

        {/* 3. HTTP Security Headers */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <Server className="w-4 h-4 text-purple-600" />
              <span>3. Security Headers Checklist</span>
            </div>
            <ul className="space-y-1.5 text-gray-700">
              <li className="flex items-center gap-1.5"><CheckCircle2 className="w-3.5 h-3.5 text-brand-green" /> X-Content-Type-Options: nosniff</li>
              <li className="flex items-center gap-1.5"><CheckCircle2 className="w-3.5 h-3.5 text-brand-green" /> X-Frame-Options: DENY</li>
              <li className="flex items-center gap-1.5"><CheckCircle2 className="w-3.5 h-3.5 text-brand-green" /> Referrer-Policy: strict-origin-when-cross-origin</li>
              <li className="flex items-center gap-1.5"><CheckCircle2 className="w-3.5 h-3.5 text-brand-green" /> Permissions-Policy: camera=(), geolocation=()</li>
            </ul>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Injectable via Cloudflare Transform Rules or Nginx.
          </span>
        </div>

        {/* 4. Session & Cookie Protection */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <KeyRound className="w-4 h-4 text-amber-600" />
              <span>4. Session & Cookie Policy</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              Zero storage of auth tokens, passwords, or payment cards in localStorage. Session cookies require:
            </p>
            <ul className="space-y-1 text-gray-700">
              <li>• <strong>HttpOnly:</strong> Blocks JavaScript document.cookie access</li>
              <li>• <strong>Secure:</strong> Only transmitted over encrypted HTTPS</li>
              <li>• <strong>SameSite=Lax:</strong> Mitigates CSRF cross-origin forgery</li>
            </ul>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Automatic timeout after 30 minutes inactivity.
          </span>
        </div>

        {/* 5. Input Validation & Injection Defense */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <FileCheck className="w-4 h-4 text-brand-orange" />
              <span>5. Validation & SQLi/XSS Defense</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              All form inputs (pincodes, seller details, contact messages) are strictly validated and normalized using structured schemas (Zod).
            </p>
            <p className="text-gray-500">
              Database queries must strictly utilize parameterized prepared statements to eliminate SQL injection vulnerabilities.
            </p>
          </div>
          <button
            onClick={() => handleAction('Review Schema Rules')}
            className="text-xs font-semibold text-brand-green hover:underline text-left cursor-pointer"
          >
            Review Schema Validation Rules &rarr;
          </button>
        </div>

        {/* 6. Rate Limiting & Abuse Prevention */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <ShieldCheck className="w-4 h-4 text-red-600" />
              <span>6. Rate Limiting & Bot Control</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              Prevents brute-force credential stuffing, checkout scraping, and inventory lockup:
            </p>
            <ul className="space-y-1 text-gray-700">
              <li>• Max 5 login attempts / IP / 15 minutes</li>
              <li>• Max 20 checkout attempts / hour</li>
              <li>• Cloudflare Turnstile bot verification on forms</li>
            </ul>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            WAF rate-limiting rules active at edge proxy.
          </span>
        </div>

        {/* 7. File Upload Restrictions */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <FileCode className="w-4 h-4 text-emerald-600" />
              <span>7. Secure Media & Upload Policies</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              Artisan product photography and KYC documents must be validated server-side by magic bytes (not file extension alone).
            </p>
            <p className="text-gray-500">
              Files must be stripped of EXIF GPS coordinates and stored in private object storage (R2 / S3) with non-executable permissions.
            </p>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Direct streaming to S3/R2 with presigned URLs.
          </span>
        </div>

        {/* 8. Dependency Audit & Secrets Policy */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <Lock className="w-4 h-4 text-blue-600" />
              <span>8. Secrets Isolation Policy</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              Zero API secrets or database credentials in frontend code or client bundles. Secrets must be managed via encrypted environment managers (KMS).
            </p>
            <p className="text-gray-500">
              Automated Dependabot / npm audit checks in CI pipeline.
            </p>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Audited October 20, 2026.
          </span>
        </div>

        {/* 9. Privacy, DPDP Act & Data Retention */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-3 flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 font-bold text-gray-900 font-display text-sm mb-2">
              <CheckCircle2 className="w-4 h-4 text-emerald-600" />
              <span>9. India DPDP Act Compliance</span>
            </div>
            <p className="text-gray-600 leading-relaxed mb-2">
              Adheres to the Digital Personal Data Protection (DPDP) Act:
            </p>
            <ul className="space-y-1 text-gray-700">
              <li>• Explicit purpose consent on customer registration</li>
              <li>• Right to erase personal address and contact history</li>
              <li>• Zero sharing of rural artisan banking data</li>
            </ul>
          </div>
          <span className="text-[10px] text-gray-400 border-t border-gray-100 pt-2 block">
            Data localized within Indian sovereign data centers.
          </span>
        </div>

      </div>

      {/* Production Readiness Callout */}
      <div className="p-6 bg-brand-cream rounded-2xl border border-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h3 className="font-bold text-sm text-gray-900 font-display">Need a custom enterprise security assessment?</h3>
          <p className="text-xs text-gray-600 mt-0.5">Review our comprehensive Phase 2 backend integration blueprint.</p>
        </div>
        <button
          onClick={() => handleAction('Download Security Whitepaper')}
          className="px-5 py-2.5 bg-brand-green hover:bg-brand-green-dark text-white text-xs font-bold rounded-xl shadow-xs shrink-0 cursor-pointer"
        >
          Download Security Blueprint (Demo)
        </button>
      </div>

    </div>
  );
};
