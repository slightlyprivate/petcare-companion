import { InputHTMLAttributes } from 'react';

type Props = InputHTMLAttributes<HTMLInputElement> & {
  label?: string;
  inlineLabel?: boolean;
};

/**
 * TextInput component with optional label.
 */
export default function TextInput({ label, inlineLabel = false, className, ...rest }: Props) {
  const input = (
    <input className={`border rounded px-3 py-1.5 w-full ${className || ''}`} {...rest} />
  );
  if (!label) return input;
  return (
    <label className={inlineLabel ? 'inline-flex items-center gap-2 text-sm' : 'space-y-1'}>
      <span className="text-sm">{label}</span>
      {inlineLabel ? <span>{input}</span> : input}
    </label>
  );
}
