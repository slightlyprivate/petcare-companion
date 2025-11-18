import { TextareaHTMLAttributes } from 'react';

type Props = TextareaHTMLAttributes<HTMLTextAreaElement> & {
  label?: string;
};

/**
 * TextArea component with optional label.
 */
export default function TextArea({ label, className, rows = 3, ...rest }: Props) {
  const area = (
    <textarea
      className={`border rounded px-3 py-2 w-full ${className || ''}`}
      rows={rows}
      {...rest}
    />
  );
  if (!label) return area;
  return (
    <label className="space-y-1">
      <span className="text-sm">{label}</span>
      {area}
    </label>
  );
}
