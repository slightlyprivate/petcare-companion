# Demo Scenario: Shared Pet Care Workflow

This scenario demonstrates the end-to-end caregiver + routine + activity flow introduced in the
pivot.

## Actors

- Owner (original pet creator)
- Caregiver (invited collaborator)
- Pet (domain aggregate for routines & activities)

## Prerequisites

1. Pet exists and is owned by the Owner.
2. Routine(s) defined for the pet (e.g., Morning Feeding at 08:00 on Mon–Sun).
3. Activities feature and caregiver invitation endpoints documented (Scribe generated).

## Steps

### 1. Owner Sends Caregiver Invitation

- Endpoint: `POST /api/pets/{pet}/caregiver-invitations`
- Response includes invitation token and expiry.
- Activity Log entry: `caregiver_invitation_sent`.

### 2. Caregiver Accepts Invitation

- Endpoint: `POST /api/caregiver-invitations/{token}/accept`
- System creates `pet_user` pivot record with role `caregiver`.
- Activity Log entry: `caregiver_invitation_accepted`.

### 3. Caregiver Views Pet Dashboard

- Endpoint(s): `GET /api/pets/{pet}`, `GET /api/pets/{pet}/caregivers`,
  `GET /api/pets/{pet}/routines/today`, `GET /api/pets/{pet}/activities`.
- UI renders caregiver list (read-only removal control hidden), today’s tasks checklist, and
  activity timeline.

### 4. Caregiver Logs an Activity

- Endpoint: `POST /api/pets/{pet}/activities`
- Example payload:

  ```json
  { "description": "Fed breakfast", "type": "feeding" }
  ```

- Activity Log entry: `pet_activity_created`.
- UI timeline updates via React Query invalidation.

### 5. Caregiver Completes a Routine Occurrence

- Endpoint: `POST /api/routine-occurrences/{occurrence}/complete`
- Marks occurrence with timestamp + `completed_by` user id.
- Activity Log entry: `pet_routine_completed`.
- Checklist progress bar updates (e.g., 1/3 tasks completed → 33%).

### 6. Owner Reviews Activity & Routine Completion

- Owner loads dashboard; sees caregiver attribution on activity and routine occurrence.
- Optional: Owner creates a new routine → future occurrences generated lazily.

### 7. Optional Revocation

- Owner may revoke caregiver access:
  - Endpoint: `DELETE /api/pets/{pet}/caregivers/{user}`
  - Activity Log entry: `caregiver_access_revoked`.

## Authorization Summary

| Action                      | Owner | Caregiver      |
| --------------------------- | ----- | -------------- |
| Invite caregiver            | ✅    | ❌             |
| Accept invitation           | N/A   | ✅ (via token) |
| Remove caregiver            | ✅    | ❌             |
| List caregivers             | ✅    | ✅             |
| List activities             | ✅    | ✅             |
| Create activity             | ✅    | ✅             |
| Delete activity             | ✅    | ❌             |
| Create routine              | ✅    | ❌             |
| Update routine              | ✅    | ❌             |
| Delete routine              | ✅    | ❌             |
| Complete routine occurrence | ✅    | ✅             |

## React Components Involved

- `CaregiverList` – invitation + listing (owner controls vs read-only state).
- `ActivityTimeline` – creation + deletion with optimistic UI refresh.
- `RoutineChecklist` – completion and progress display.

## Data Flows

1. Mutation → Laravel Controller → Service Layer → Eloquent Model → Spatie Activity Log.
2. React Query invalidates corresponding cache key (e.g., `activities.byPet(petId)`), triggering
   refetch.

## Logging & Traceability

Spatie Activity Log records caregiver-related events, routine completions, and activity
creation/deletion. This enables audit trails useful for future reporting or notification triggers.

## Extension Ideas

- Notifications (email/SMS) on routine overdue states.
- Media attachments for activities (photo of walk or meal).
- Caregiver scheduling preferences.

## Verification Checklist (Scenario Completion)

- Invitation accepted and caregiver appears in `GET /api/pets/{pet}/caregivers`.
- Activity created and visible in timeline.
- Routine occurrence completion recorded and progress updated.
- All relevant events present in Activity Log.

---

This scenario encapsulates the pivot's value proposition: collaborative, structured pet care with
clear action history.
