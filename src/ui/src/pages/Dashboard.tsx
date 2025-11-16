import { useMe } from '../api/auth/hooks';

/**
 * Dashboard page displaying user information.
 */
export default function Dashboard() {
  const { data: me } = useMe();
  return (
    <div>
      <h1 className="text-xl font-semibold mb-3">Dashboard</h1>
      <pre className="bg-gray-50 border rounded p-3 text-sm">{JSON.stringify(me, null, 2)}</pre>
    </div>
  );
}
