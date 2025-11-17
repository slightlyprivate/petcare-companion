/**
 * Component to display an error message in red text.
 */
export default function ErrorMessage({ message }: { message: string }) {
  return <div className="text-red-600 text-sm">{message}</div>;
}
