import PetForm, { type PetFormValues } from '../../components/forms/PetForm';
import { useCreatePet } from '../../api/pets/hooks';
import { useToast } from '../../lib/notifications';
import { useNavigate } from 'react-router-dom';
import { ensureCsrf } from '../../lib/csrf';
import { PATHS } from '../../routes/paths';

/**
 * Pet creation page using a reusable form component.
 */
export default function PetNew() {
  const createPet = useCreatePet();
  const toast = useToast();
  const navigate = useNavigate();

  async function onSubmit(values: PetFormValues) {
    await ensureCsrf();
    createPet.mutate(values, {
      onSuccess: () => {
        toast.success('Pet created');
        navigate(PATHS.DASHBOARD.PETS, { replace: true });
      },
      onError: (err: any) => toast.error(err?.message || 'Failed to create pet'),
    });
  }

  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Add a New Pet</h1>
      <div className="text-sm text-brand-fg mb-4">Fill out the details below.</div>
      <PetForm onSubmit={onSubmit} isSubmitting={createPet.isPending} submitLabel="Create Pet" />
    </div>
  );
}
