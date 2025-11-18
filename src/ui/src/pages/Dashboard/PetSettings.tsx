import { useParams } from 'react-router-dom';
import PetSettingsForm, { type PetSettingsValues } from '../../components/forms/PetSettingsForm';
import { useToast } from '../../lib/notifications';

/**
 * Pet settings page using a reusable settings form component.
 */
export default function PetSettings() {
  const { id } = useParams();
  const toast = useToast();

  function onSubmit(_values: PetSettingsValues) {
    // Placeholder: would call an API to persist settings
    toast.success(`Settings saved for pet #${id}`);
  }

  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Pet Settings</h1>
      <div className="text-sm text-brand-fg mb-4">
        Manage visibility and public profile for Pet #{id}.
      </div>
      <PetSettingsForm onSubmit={onSubmit} />
    </div>
  );
}
