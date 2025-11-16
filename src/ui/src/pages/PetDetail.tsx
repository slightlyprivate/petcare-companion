import { useParams } from 'react-router-dom';
import QueryBoundary from '../components/QueryBoundary';
import { usePublicPet } from '../api/pets/hooks';

/**
 * Pet detail page displaying information about a specific pet.
 */
export default function PetDetail() {
  const { id } = useParams();
  const { data: pet, isLoading, error } = usePublicPet(id!);

  return (
    <div>
      <h1 className="text-xl font-semibold mb-3">Pet Detail</h1>
      <QueryBoundary loading={isLoading} error={error}>
        {pet ? (
          <div className="border rounded p-4">
            <div className="font-medium">{pet.name}</div>
            <div className="text-sm text-gray-600">{pet.species}</div>
          </div>
        ) : (
          <div>No pet found.</div>
        )}
      </QueryBoundary>
    </div>
  );
}
