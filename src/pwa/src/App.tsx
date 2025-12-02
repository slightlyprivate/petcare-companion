import './app.css';
import { AppVersionFooter } from './components/AppVersionFooter';

export function App() {
  return (
    <main className="ui-shell">
      <div>
        <header>
          <p className="eyebrow">PetCare Companion · PWA Experience</p>
          <h1>Daily Caregiving Workflows</h1>
          <p className="lede">
            This Progressive Web App delivers the core caregiving experience for PetCare Companion.
            Manage daily pet care, log activities, complete routines, and collaborate with other
            caregivers—all in a mobile-first, installable interface.
          </p>
        </header>
        <section>
          <h2>Roadmap Snapshot</h2>
          <ul>
            <li>Pet dashboard with activity timeline</li>
            <li>Routine management and completion tracking</li>
            <li>Activity logging (feeding, walks, medications, etc.)</li>
            <li>Caregiver invitations and shared care workflows</li>
            <li>Media uploads and pet journals</li>
          </ul>
        </section>
        <section>
          <h2>Status</h2>
          <p>
            The PWA scaffold is ready for feature development. Build daily caregiving interfaces
            here, keep shared primitives under <code>src/lib</code>, and focus on mobile-responsive
            experiences. Administrative flows (account, billing, settings) live in the separate UI
            project at <code>src/ui</code>.
          </p>
        </section>
      </div>
      <AppVersionFooter />
    </main>
  );
}

export default App;
