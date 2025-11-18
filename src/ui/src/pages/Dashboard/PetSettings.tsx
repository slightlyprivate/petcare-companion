import { useParams } from 'react-router-dom';

/**
 * Pet settings page.
 * @returns Pet settings component
 */
export default function PetSettings() {
  const { id } = useParams();
  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Pet Settings</h1>
      <div className="text-sm text-brand-fg">
        Manage visibility and public profile for Pet #{id}.
      </div>
    </div>
  );
}
