import React, { Component, ErrorInfo, ReactNode } from 'react';
import { AlertTriangle, RefreshCw, Home } from 'lucide-react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
  public state: State = {
    hasError: false,
    error: null,
  };

  public static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('GanvGaliHut Uncaught System Exception caught by ErrorBoundary:', error, errorInfo);
  }

  private handleReset = () => {
    this.setState({ hasError: false, error: null });
    window.location.href = '/';
  };

  private handleReload = () => {
    window.location.reload();
  };

  public render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
          <div className="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center space-y-6">
            <div className="w-16 h-16 rounded-2xl bg-amber-100 text-brand-orange flex items-center justify-center mx-auto shadow-inner">
              <AlertTriangle className="w-8 h-8" />
            </div>

            <div className="space-y-2">
              <span className="text-[11px] font-bold text-brand-orange uppercase tracking-wider">
                Resilience Guard Active
              </span>
              <h1 className="text-2xl font-extrabold text-gray-900 font-display">
                Something Encountered a Snag
              </h1>
              <p className="text-xs text-gray-600 leading-relaxed">
                To guarantee uninterrupted browsing for millions of users, the GanvGaliHut edge resilience system isolated this module.
              </p>
            </div>

            {this.state.error && (
              <div className="p-3 bg-gray-100 rounded-xl text-left overflow-x-auto text-[11px] font-mono text-gray-700 max-h-32">
                {this.state.error.message || 'Unknown runtime anomaly'}
              </div>
            )}

            <div className="flex flex-col sm:flex-row gap-3 pt-2">
              <button
                onClick={this.handleReload}
                className="flex-1 py-3 px-4 bg-brand-green hover:bg-brand-green-dark text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer"
              >
                <RefreshCw className="w-4 h-4" />
                <span>Reload Page</span>
              </button>

              <button
                onClick={this.handleReset}
                className="flex-1 py-3 px-4 bg-white border border-gray-200 hover:border-brand-green text-gray-800 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer"
              >
                <Home className="w-4 h-4 text-brand-green" />
                <span>Return to Home</span>
              </button>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
