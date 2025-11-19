import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';

/* eslint-disable react-refresh/only-export-components */

export type Toast = {
  id: string;
  type?: 'info' | 'success' | 'error' | 'warning';
  message: string;
  timeoutMs?: number;
};

type Ctx = {
  toasts: Toast[];
  push: (toast: Omit<Toast, 'id'>) => void;
  remove: (id: string) => void;
  clear: () => void;
};

const NotificationsContext = createContext<Ctx | null>(null);

export function NotificationsProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const remove = useCallback((id: string) => {
    setToasts((t) => t.filter((x) => x.id !== id));
  }, []);

  const push = useCallback(
    (toast: Omit<Toast, 'id'>) => {
      const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
      const entry: Toast = { id, type: 'info', timeoutMs: 4000, ...toast };
      setToasts((t) => [entry, ...t]);
      if (entry.timeoutMs && entry.timeoutMs > 0) {
        setTimeout(() => remove(id), entry.timeoutMs);
      }
    },
    [remove],
  );

  const clear = useCallback(() => setToasts([]), []);

  const value = useMemo(() => ({ toasts, push, remove, clear }), [toasts, push, remove, clear]);

  return (
    <NotificationsContext.Provider value={value}>
      {children}
      <div className="fixed top-2 right-2 z-50 space-y-2">
        {toasts.map((t) => (
          <div
            key={t.id}
            role="status"
            className={`min-w-[240px] max-w-sm rounded border px-3 py-2 shadow text-sm bg-white ${
              t.type === 'success'
                ? 'border-green-300'
                : t.type === 'error'
                  ? 'border-red-300'
                  : t.type === 'warning'
                    ? 'border-yellow-300'
                    : 'border-gray-300'
            }`}
          >
            <div className="flex items-start justify-between gap-3">
              <span className="leading-snug">{t.message}</span>
              <button
                className="text-gray-500 hover:text-gray-700"
                aria-label="Dismiss notification"
                onClick={() => remove(t.id)}
              >
                ×
              </button>
            </div>
          </div>
        ))}
      </div>
    </NotificationsContext.Provider>
  );
}

export function useToast() {
  const ctx = useContext(NotificationsContext);
  if (!ctx) throw new Error('useToast must be used within NotificationsProvider');
  return {
    info: (message: string, timeoutMs?: number) => ctx.push({ type: 'info', message, timeoutMs }),
    success: (message: string, timeoutMs?: number) =>
      ctx.push({ type: 'success', message, timeoutMs }),
    error: (message: string, timeoutMs?: number) => ctx.push({ type: 'error', message, timeoutMs }),
    warning: (message: string, timeoutMs?: number) =>
      ctx.push({ type: 'warning', message, timeoutMs }),
    clear: ctx.clear,
  };
}
