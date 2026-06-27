import { NextRequest, NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';

const UPLOADS_DIR = path.join(process.cwd(), 'public', 'uploads');

const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const ALLOWED_AUDIO_TYPES = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/aac', 'audio/ogg', 'audio/flac'];
const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_AUDIO_SIZE = 100 * 1024 * 1024; // 100MB

function ensureUploadsDir(): void {
  if (!fs.existsSync(UPLOADS_DIR)) {
    fs.mkdirSync(UPLOADS_DIR, { recursive: true });
  }
}

export async function POST(request: NextRequest): Promise<NextResponse> {
  try {
    ensureUploadsDir();

    const formData = await request.formData();
    const avatarFile = formData.get('avatar') as File | null;
    const audioFile = formData.get('audio') as File | null;

    if (!avatarFile && !audioFile) {
      return NextResponse.json({ success: false, error: 'No files provided' }, { status: 400 });
    }

    const result: { success: boolean; avatarPath?: string; audioPath?: string; error?: string } = {
      success: true,
    };

    if (avatarFile) {
      if (!ALLOWED_IMAGE_TYPES.includes(avatarFile.type)) {
        return NextResponse.json(
          { success: false, error: `Invalid image type: ${avatarFile.type}. Allowed: JPEG, PNG, WebP` },
          { status: 400 }
        );
      }
      if (avatarFile.size > MAX_IMAGE_SIZE) {
        return NextResponse.json(
          { success: false, error: 'Avatar image exceeds 10MB limit' },
          { status: 400 }
        );
      }

      const ext = path.extname(avatarFile.name) || '.jpg';
      const filename = `avatar_${uuidv4()}${ext}`;
      const filePath = path.join(UPLOADS_DIR, filename);
      const buffer = Buffer.from(await avatarFile.arrayBuffer());
      fs.writeFileSync(filePath, buffer);
      result.avatarPath = `/uploads/${filename}`;
    }

    if (audioFile) {
      // Some browsers send audio/mpeg or audio/mp3 interchangeably
      const audioType = audioFile.type || 'audio/mpeg';
      if (!ALLOWED_AUDIO_TYPES.includes(audioType) && !audioType.startsWith('audio/')) {
        return NextResponse.json(
          { success: false, error: `Invalid audio type: ${audioType}. Allowed: MP3, WAV, AAC, OGG, FLAC` },
          { status: 400 }
        );
      }
      if (audioFile.size > MAX_AUDIO_SIZE) {
        return NextResponse.json(
          { success: false, error: 'Audio file exceeds 100MB limit' },
          { status: 400 }
        );
      }

      const ext = path.extname(audioFile.name) || '.mp3';
      const filename = `audio_${uuidv4()}${ext}`;
      const filePath = path.join(UPLOADS_DIR, filename);
      const buffer = Buffer.from(await audioFile.arrayBuffer());
      fs.writeFileSync(filePath, buffer);
      result.audioPath = `/uploads/${filename}`;
    }

    return NextResponse.json(result);
  } catch (error) {
    console.error('[upload] Error:', error);
    return NextResponse.json(
      { success: false, error: 'Upload failed. Please try again.' },
      { status: 500 }
    );
  }
}
