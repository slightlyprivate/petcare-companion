/**
 * Component to display an error message in red text.
 */
export default function ErrorMessage({ message }: { message: string }) {
  return <div className="text-brand-danger text-sm">{message}</div>;
}
