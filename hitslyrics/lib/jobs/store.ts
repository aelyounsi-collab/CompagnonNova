import fs from 'fs';
import path from 'path';
import { LipSyncJob } from '@/types';

// TODO: Phase 2 - Replace with a real database (PostgreSQL, MongoDB, or Redis)
// For V1 we persist jobs as a single JSON file on the local filesystem

const JOBS_FILE = path.join(process.cwd(), 'public', 'uploads', '_jobs.json');

function ensureJobsFile(): void {
  const dir = path.dirname(JOBS_FILE);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
  if (!fs.existsSync(JOBS_FILE)) {
    fs.writeFileSync(JOBS_FILE, JSON.stringify([], null, 2));
  }
}

export function getAllJobs(): LipSyncJob[] {
  ensureJobsFile();
  try {
    const raw = fs.readFileSync(JOBS_FILE, 'utf-8');
    return JSON.parse(raw) as LipSyncJob[];
  } catch {
    return [];
  }
}

export function getJobById(id: string): LipSyncJob | null {
  const jobs = getAllJobs();
  return jobs.find((j) => j.id === id) ?? null;
}

export function saveJob(job: LipSyncJob): void {
  ensureJobsFile();
  const jobs = getAllJobs();
  const idx = jobs.findIndex((j) => j.id === job.id);
  if (idx >= 0) {
    jobs[idx] = job;
  } else {
    jobs.push(job);
  }
  fs.writeFileSync(JOBS_FILE, JSON.stringify(jobs, null, 2));
}

export function updateJobStatus(
  id: string,
  updates: Partial<LipSyncJob>
): LipSyncJob | null {
  const job = getJobById(id);
  if (!job) return null;
  const updated: LipSyncJob = {
    ...job,
    ...updates,
    updatedAt: new Date().toISOString(),
  };
  saveJob(updated);
  return updated;
}
