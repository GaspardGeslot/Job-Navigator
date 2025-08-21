/**
 * Helper for managing candidate view history in localStorage
 */

interface CandidateView {
  email: string;
  lastViewed: string; // ISO date string
}

interface CandidatesViewed {
  [email: string]: CandidateView;
}

const STORAGE_KEY = 'candidatesViewed';

/**
 * Get all viewed candidates from localStorage
 */
export function getCandidatesViewed(): CandidatesViewed {
  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    return stored ? JSON.parse(stored) : {};
  } catch (error) {
    console.error('Error reading candidatesViewed from localStorage:', error);
    return {};
  }
}

/**
 * Save viewed candidates to localStorage
 */
function saveCandidatesViewed(candidatesViewed: CandidatesViewed): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(candidatesViewed));
  } catch (error) {
    console.error('Error saving candidatesViewed to localStorage:', error);
  }
}

/**
 * Mark a candidate as viewed by email
 */
export function markCandidateAsViewed(email: string): void {
  if (!email) return;

  const candidatesViewed = getCandidatesViewed();
  const now = new Date().toISOString();

  candidatesViewed[email] = {
    email,
    lastViewed: now,
  };

  saveCandidatesViewed(candidatesViewed);
}

/**
 * Check if a candidate has been viewed
 */
export function isCandidateViewed(email: string): boolean {
  if (!email) return false;
  console.log('email : ', email);
  const candidatesViewed = getCandidatesViewed();
  const isViewed = email in candidatesViewed;
  console.log('isViewed : ', isViewed);
  return isViewed;
}

/**
 * Get the last viewed time for a candidate
 */
export function getCandidateLastViewed(email: string): Date | null {
  if (!email) return null;

  const candidatesViewed = getCandidatesViewed();
  const candidate = candidatesViewed[email];

  if (!candidate) return null;

  try {
    return new Date(candidate.lastViewed);
  } catch (error) {
    console.error('Error parsing lastViewed date:', error);
    return null;
  }
}

/**
 * Remove a candidate from viewed history
 */
export function removeCandidateFromViewed(email: string): void {
  if (!email) return;

  const candidatesViewed = getCandidatesViewed();
  delete candidatesViewed[email];
  saveCandidatesViewed(candidatesViewed);
}

/**
 * Clear all viewed candidates history
 */
export function clearCandidatesViewed(): void {
  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch (error) {
    console.error('Error clearing candidatesViewed from localStorage:', error);
  }
}

/**
 * Get viewed candidates count
 */
export function getViewedCandidatesCount(): number {
  const candidatesViewed = getCandidatesViewed();
  return Object.keys(candidatesViewed).length;
}
