import { NextRequest, NextResponse } from 'next/server';
import { v4 as uuidv4 } from 'uuid';
import { saveJob, getAllJobs } from '@/lib/jobs/store';
import { LipSyncJob, JobCreateResponse, AudioAnalysis } from '@/types';

export async function POST(request: NextRequest): Promise<NextResponse> {
  try {
    const body = await request.json();
    const {
      avatarPath,
      audioPath,
      avatarOriginalName,
      audioOriginalName,
      analysis,
    } = body as {
      avatarPath?: string;
      audioPath?: string;
      avatarOriginalName?: string;
      audioOriginalName?: string;
      analysis?: AudioAnalysis;
    };

    if (!avatarPath || !audioPath) {
      return NextResponse.json<JobCreateResponse>(
        { success: false, error: 'avatarPath and audioPath are required' },
        { status: 400 }
      );
    }

    if (!analysis) {
      return NextResponse.json<JobCreateResponse>(
        { success: false, error: 'Audio analysis is required before creating a job' },
        { status: 400 }
      );
    }

    const job: LipSyncJob = {
      id: uuidv4(),
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      status: 'pending',
      avatarPath,
      audioPath,
      avatarOriginalName: avatarOriginalName ?? 'avatar',
      audioOriginalName: audioOriginalName ?? 'audio',
      analysis,
      engine: 'none',
      outputPath: null,
      error: null,
      progress: 0,
    };

    saveJob(job);

    return NextResponse.json<JobCreateResponse>({ success: true, job }, { status: 201 });
  } catch (error) {
    console.error('[jobs POST] Error:', error);
    return NextResponse.json<JobCreateResponse>(
      { success: false, error: 'Failed to create job' },
      { status: 500 }
    );
  }
}

export async function GET(): Promise<NextResponse> {
  const jobs = getAllJobs();
  return NextResponse.json({ success: true, jobs });
}
