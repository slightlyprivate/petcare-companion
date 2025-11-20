import { useParams } from 'react-router-dom';
import QueryBoundary from '../../components/QueryBoundary';
import { usePet } from '../../api/pets/hooks';
import { useMe } from '../../api/auth/hooks';
import CaregiverList from '../../components/caregivers/CaregiverList';
import ActivityTimeline from '../../components/activities/ActivityTimeline';
import RoutineChecklist from '../../components/routines/RoutineChecklist';

/**
 * Pet detail page displaying information about a specific pet.
 */
export default function PetDetail() {
  const { id } = useParams();
  const { data: pet, isLoading, error } = usePet(id!);
  const { data: currentUser } = useMe();

  const isOwner = pet && currentUser ? pet.user_id === currentUser.id : false;

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-semibold mb-3">Pet Detail</h1>
      <QueryBoundary loading={isLoading} error={error}>
        {pet ? (
          <div className="space-y-6">
            <div className="border rounded p-4">
              <div className="font-medium">{pet.name}</div>
              <div className="text-sm text-gray-600">{pet.species}</div>
              {pet.breed && <div className="text-sm text-gray-500">{pet.breed}</div>}
              {pet.age !== undefined && pet.age !== null && (
                <div className="text-sm text-gray-500">{pet.age} years old</div>
              )}
            </div>

            <RoutineChecklist petId={pet.id} canComplete={true} />

            <CaregiverList petId={pet.id} isOwner={isOwner} />

            <ActivityTimeline petId={pet.id} canCreate={true} canDelete={isOwner} />
          </div>
        ) : (
          <div>No pet found.</div>
        )}
      </QueryBoundary>
    </div>
  );
}
