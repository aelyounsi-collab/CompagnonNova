'use client';

import Image from 'next/image';

interface AvatarPreviewProps {
  src: string;
  filename: string;
}

export default function AvatarPreview({ src, filename }: AvatarPreviewProps) {
  return (
    <div className="flex flex-col items-center gap-2">
      {/* 9:16 portrait frame */}
      <div className="relative bg-zinc-900 rounded-xl overflow-hidden border border-zinc-700"
        style={{ width: 120, height: 213 }}>
        <Image
          src={src}
          alt="Avatar preview"
          fill
          className="object-cover"
          unoptimized
        />
        <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent py-1 px-2">
          <p className="text-xs text-zinc-300 text-center">9:16</p>
        </div>
      </div>
      <p className="text-xs text-zinc-500 truncate max-w-[120px]">{filename}</p>
    </div>
  );
}
