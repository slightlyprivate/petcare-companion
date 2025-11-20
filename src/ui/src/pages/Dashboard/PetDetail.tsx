import { useParams } from 'react-router-dom';
import QueryBoundary from '../../components/QueryBoundary';
import { usePet } from '../../api/pets/hooks';
import { useMe } from '../../api/auth/hooks';
import CaregiverList from '../../components/caregivers/CaregiverList';
import ActivityTimeline from '../../components/activities/ActivityTimeline';
import RoutineChecklist from '../../components/routines/RoutineChecklist';
import Tabs from '../../components/layout/Tabs';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '../../components/ui/Card';
import { ErrorBoundary } from '../../components/ui/ErrorBoundary';

/**
 * Pet detail page displaying information about a specific pet.
 */
export default function PetDetail() {
  const { id } = useParams();
  const { data: pet, isLoading, error } = usePet(id!);
  const { data: currentUser } = useMe();

  const isOwner = pet && currentUser ? pet.user_id === currentUser.id : false;

  const tabs = pet
    ? [
        {
          id: 'routines',
          label: 'Routines',
          content: (
            <ErrorBoundary>
              <RoutineChecklist petId={pet.id} canComplete={true} />
            </ErrorBoundary>
          ),
        },
        {
          id: 'caregivers',
          label: 'Caregivers',
          content: (
            <ErrorBoundary>
              <CaregiverList petId={pet.id} isOwner={isOwner} />
            </ErrorBoundary>
          ),
        },
        {
          id: 'activities',
          label: 'Activities',
          content: (
            <ErrorBoundary>
              <ActivityTimeline petId={pet.id} canCreate={true} canDelete={isOwner} />
            </ErrorBoundary>
          ),
        },
      ]
    : [];

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-semibold mb-3">Pet Detail</h1>
      <QueryBoundary loading={isLoading} error={error}>
        {pet ? (
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>{pet.name}</CardTitle>
                <CardDescription className="flex flex-col gap-0.5">
                  <span className="capitalize">{pet.species}</span>
                  {pet.breed && <span className="text-gray-500">{pet.breed}</span>}
                  {pet.age !== undefined && pet.age !== null && (
                    <span className="text-gray-500">{pet.age} years old</span>
                  )}
                </CardDescription>
              </CardHeader>
            </Card>
            <Tabs tabs={tabs} />
          </div>
        ) : (
          <div>No pet found.</div>
        )}
      </QueryBoundary>
    </div>
  );
}
