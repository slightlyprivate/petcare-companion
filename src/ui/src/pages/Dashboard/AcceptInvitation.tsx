import { useEffect, useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useAcceptCaregiverInvitation } from '../../api/caregivers/hooks';
import { PATHS } from '../../routes/paths';
import InvitationStatusCard from '../../components/invitations/InvitationStatusCard';
import { parseInvitationError, extractPetInfo } from '../../utils/invitationHelpers';

/**
 * AcceptInvitation Page
 *
 * Handles accepting caregiver invitations via a token provided in the URL query parameters.
 */
export default function AcceptInvitation() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const token = searchParams.get('token');
  const [hasAttempted, setHasAttempted] = useState(false);

  const acceptInvitation = useAcceptCaregiverInvitation();

  useEffect(() => {
    if (token && !hasAttempted) {
      setHasAttempted(true);
      acceptInvitation.mutate(token);
    }
  }, [token, hasAttempted, acceptInvitation]);

  const navigateToDashboard = () => navigate(PATHS.DASHBOARD.PETS);
  const navigateToPet = (petId?: string) => {
    if (petId) {
      navigate(PATHS.DASHBOARD.PET_DETAIL(petId));
    } else {
      navigateToDashboard();
    }
  };

  // No token provided
  if (!token) {
    return (
      <InvitationStatusCard
        type="invalid"
        title="Invalid Invitation"
        message="This invitation link is invalid or incomplete. Please check the link and try again."
        onPrimaryAction={navigateToDashboard}
        primaryLabel="Go to Dashboard"
      />
    );
  }

  // Loading state
  if (acceptInvitation.isPending) {
    return (
      <InvitationStatusCard
        type="loading"
        title="Accepting Invitation..."
        message="Please wait while we process your invitation."
        showSpinner
      />
    );
  }

  // Error state
  if (acceptInvitation.isError) {
    const { title, message } = parseInvitationError(
      acceptInvitation.error as Error & { status?: number },
    );

    return (
      <InvitationStatusCard
        type="error"
        title={title}
        message={message}
        onPrimaryAction={() => window.location.reload()}
        onSecondaryAction={navigateToDashboard}
        primaryLabel="Try Again"
        secondaryLabel="Go to Dashboard"
      />
    );
  }

  // Success state
  if (acceptInvitation.isSuccess) {
    const pet = extractPetInfo(acceptInvitation.data);

    // Auto-redirect after 2 seconds
    useEffect(() => {
      const timer = setTimeout(() => navigateToPet(pet.id), 2000);
      return () => clearTimeout(timer);
    }, [pet.id]);

    return (
      <InvitationStatusCard
        type="success"
        title="Invitation Accepted!"
        message={`You are now a caregiver${pet.name ? ` for ${pet.name}` : ''}.`}
        subMessage="Redirecting you to the pet dashboard..."
        onPrimaryAction={() => navigateToPet(pet.id)}
        primaryLabel="Go Now"
      />
    );
  }

  return null;
}
