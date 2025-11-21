import { useParams } from 'react-router-dom';
import PetSettingsForm, { type PetSettingsValues } from '../../components/forms/PetSettingsForm';
import PetAvatarUploader from '../../components/PetAvatarUploader';
import { usePet, useUpdatePet } from '../../api/pets/hooks';
import { useAvatarUpload } from '../../hooks';
import { useToast } from '../../lib/notifications';
import QueryBoundary from '../../components/QueryBoundary';

/**
 * Pet settings page for managing avatar and visibility options.
 */
export default function PetSettings() {
  const { id } = useParams<{ id: string }>();
  const toast = useToast();
  const { data: pet, isLoading, error, refetch } = usePet(id!);
  const { mutateAsync: updatePet, isPending: isUpdating } = useUpdatePet();

  const { upload: uploadAvatar } = useAvatarUpload({
    petId: id!,
    onSuccess: () => {
      toast.success('Avatar uploaded successfully');
      void refetch();
    },
    onError: (err) => toast.error(err.message),
  });

  async function handleSettingsSubmit(values: PetSettingsValues) {
    try {
      await updatePet({
        id: id!,
        name: pet!.name,
        species: pet!.species,
        owner_name: pet!.owner_name || '',
        breed: pet!.breed,
        birth_date: pet!.birth_date,
        ...values,
      });
      toast.success('Settings saved successfully');
      void refetch();
    } catch {
      toast.error('Failed to save settings');
    }
  }

  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Pet Settings</h1>
      <div className="text-sm text-brand-fg mb-4">
        Manage avatar, visibility, and public profile for {pet?.name || `Pet #${id}`}.
      </div>

      <QueryBoundary loading={isLoading} error={error}>
        {pet && (
          <div className="space-y-6">
            <PetAvatarUploader
              petId={pet.id}
              currentAvatarUrl={pet.avatar_url}
              onUploadFile={uploadAvatar}
              onUploadSuccess={() => {}}
            />

            <hr />

            <PetSettingsForm
              initial={{ is_public: pet.is_public }}
              onSubmit={handleSettingsSubmit}
              isSubmitting={isUpdating}
            />
          </div>
        )}
      </QueryBoundary>
    </div>
  );
}
