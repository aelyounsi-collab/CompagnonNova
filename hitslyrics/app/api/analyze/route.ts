import { NextRequest, NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';
import { analyzeAudio } from '@/lib/audio/analyzer';
import { AnalysisResponse } from '@/types';

export async function POST(request: NextRequest): Promise<NextResponse> {
  try {
    const body = await request.json();
    const { audioPath } = body as { audioPath?: string };

    if (!audioPath) {
      return NextResponse.json<AnalysisResponse>(
        { success: false, error: 'audioPath is required' },
        { status: 400 }
      );
    }

    // Sanitize path - must be under /uploads
    const relativePath = audioPath.startsWith('/uploads/')
      ? audioPath.slice('/uploads/'.length)
      : null;

    if (!relativePath || relativePath.includes('..')) {
      return NextResponse.json<AnalysisResponse>(
        { success: false, error: 'Invalid audio path' },
        { status: 400 }
      );
    }

    const absolutePath = path.join(process.cwd(), 'public', 'uploads', relativePath);
    if (!fs.existsSync(absolutePath)) {
      return NextResponse.json<AnalysisResponse>(
        { success: false, error: 'Audio file not found' },
        { status: 404 }
      );
    }

    const stats = fs.statSync(absolutePath);
    const analysis = await analyzeAudio(absolutePath, stats.size);

    // Save analysis JSON alongside the audio file
    const analysisPath = absolutePath.replace(/\.[^.]+$/, '_analysis.json');
    fs.writeFileSync(analysisPath, JSON.stringify(analysis, null, 2));

    return NextResponse.json<AnalysisResponse>({ success: true, analysis });
  } catch (error) {
    console.error('[analyze] Error:', error);
    return NextResponse.json<AnalysisResponse>(
      { success: false, error: 'Audio analysis failed' },
      { status: 500 }
    );
  }
}
