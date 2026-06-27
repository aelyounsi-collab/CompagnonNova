import { NextRequest, NextResponse } from 'next/server';
import { getJobById, updateJobStatus } from '@/lib/jobs/store';
import { JobStatusResponse, LipSyncEngine } from '@/types';

export async function GET(
  _request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
): Promise<NextResponse> {
  const { id } = await params;
  const job = getJobById(id);
  if (!job) {
    return NextResponse.json<JobStatusResponse>(
      { success: false, error: 'Job not found' },
      { status: 404 }
    );
  }
  return NextResponse.json<JobStatusResponse>({ success: true, job });
}

export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
): Promise<NextResponse> {
  const { id } = await params;
  const body = await request.json();

  const allowedFields = ['status', 'engine', 'progress', 'outputPath', 'error'];
  const updates: Record<string, unknown> = {};
  for (const field of allowedFields) {
    if (field in body) updates[field] = body[field];
  }

  const updated = updateJobStatus(id, updates);
  if (!updated) {
    return NextResponse.json<JobStatusResponse>(
      { success: false, error: 'Job not found' },
      { status: 404 }
    );
  }

  return NextResponse.json<JobStatusResponse>({ success: true, job: updated });
}
