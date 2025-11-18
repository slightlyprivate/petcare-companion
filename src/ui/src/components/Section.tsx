import { ReactNode } from 'react';

type Props = {
  title?: string;
  actions?: ReactNode;
  children: ReactNode;
  className?: string;
};

/**
 * Section component for grouping related content with an optional title and actions.
 */
export default function Section({ title, actions, children, className }: Props) {
  return (
    <section className={className}>
      {title ? (
        <h2 className="text-lg font-medium mb-2">
          <span className="align-middle">{title}</span>
          {actions ? <span className="ml-2 align-middle">{actions}</span> : null}
        </h2>
      ) : null}
      <div className="border rounded p-4 space-y-3 bg-white">{children}</div>
    </section>
  );
}
