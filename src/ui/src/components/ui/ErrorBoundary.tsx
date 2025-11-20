import React from 'react';

interface ErrorBoundaryProps {
  children: React.ReactNode;
  fallback?: (error: Error) => React.ReactNode;
}

interface ErrorBoundaryState {
  error: Error | null;
}

/**
 * ErrorBoundary Component
 *
 * A React component that catches JavaScript errors anywhere in its child component tree,
 * logs those errors, and displays a fallback UI instead of the component tree that crashed.
 */
export class ErrorBoundary extends React.Component<ErrorBoundaryProps, ErrorBoundaryState> {
  state: ErrorBoundaryState = { error: null };

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { error };
  }

  componentDidCatch(error: Error, info: React.ErrorInfo) {
    // eslint-disable-next-line no-console
    console.error('ErrorBoundary caught error', error, info);
  }

  render() {
    if (this.state.error) {
      if (this.props.fallback) return this.props.fallback(this.state.error);
      return (
        <div className="p-4 border rounded bg-red-50 text-sm text-red-700">
          <div className="font-semibold mb-1">Something went wrong.</div>
          <pre className="whitespace-pre-wrap text-xs">{this.state.error.message}</pre>
        </div>
      );
    }
    return this.props.children;
  }
}
