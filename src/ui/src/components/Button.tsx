import { ButtonHTMLAttributes } from 'react';

/**
 * Reusable Button component with default styling.
 */
export default function Button({
  className = '',
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      className={`inline-flex items-center px-3 py-1.5 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-50 ${className}`}
      {...props}
    />
  );
}
