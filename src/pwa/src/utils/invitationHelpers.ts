/**
 * Parses invitation acceptance errors and returns appropriate user-facing messages
 */
export function parseInvitationError(error: Error & { status?: number }): {
  title: string;
  message: string;
} {
  const errorMessage = error.message.toLowerCase();

  if (errorMessage.includes('expired')) {
    return {
      title: 'Invitation Expired',
      message: 'This invitation has expired. Please request a new invitation from the pet owner.',
    };
  }

  if (errorMessage.includes('not found') || error.status === 404) {
    return {
      title: 'Invitation Not Found',
      message: 'This invitation could not be found. It may have been revoked or already used.',
    };
  }

  if (errorMessage.includes('email') || error.status === 403) {
    return {
      title: 'Email Mismatch',
      message:
        'This invitation was sent to a different email address. Please sign in with the correct account.',
    };
  }

  if (errorMessage.includes('already has access')) {
    return {
      title: 'Already a Caregiver',
      message: 'You already have access to this pet.',
    };
  }

  return {
    title: 'Invitation Error',
    message: 'Failed to accept invitation. Please try again or contact support.',
  };
}

/**
 * Extracts pet information from invitation acceptance response
 */
export function extractPetInfo(data: unknown): { id?: string; name?: string } {
  const response = data as { pet?: { id: string; name: string } };
  return {
    id: response?.pet?.id,
    name: response?.pet?.name,
  };
}

/**
 * Converts a date string to a relative time string (e.g., "2h ago", "3d ago")
 */
export function getRelativeTime(dateString: string): string {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (diffInSeconds < 60) return 'just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
  return date.toLocaleDateString();
}

/**
 * Gets expiry status with urgency-based color coding
 */
export function getExpiryStatus(expiresAt: string): { text: string; color: string } {
  const expiry = new Date(expiresAt);
  const now = new Date();
  const diffInHours = (expiry.getTime() - now.getTime()) / (1000 * 60 * 60);

  if (diffInHours < 0) return { text: 'Expired', color: 'text-red-600' };
  if (diffInHours < 24)
    return { text: `Expires in ${Math.floor(diffInHours)}h`, color: 'text-orange-600' };
  if (diffInHours < 168)
    return { text: `Expires in ${Math.floor(diffInHours / 24)}d`, color: 'text-yellow-600' };
  return { text: `Expires ${expiry.toLocaleDateString()}`, color: 'text-brand-fg/60' };
}

/**
 * Copies invitation link to clipboard
 */
export async function copyInvitationLink(token: string): Promise<string> {
  const inviteUrl = `${window.location.origin}/caregiver-invitations/accept?token=${token}`;
  await navigator.clipboard.writeText(inviteUrl);
  return inviteUrl;
}
