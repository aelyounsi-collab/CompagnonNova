'use client';

import { useRef, useState, DragEvent, ChangeEvent } from 'react';

interface UploadCardProps {
  label: string;
  accept: string;
  icon: string;
  hint: string;
  onFile: (file: File) => void;
  disabled?: boolean;
}

export default function UploadCard({ label, accept, icon, hint, onFile, disabled }: UploadCardProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [dragging, setDragging] = useState(false);

  function handleDrop(e: DragEvent<HTMLDivElement>) {
    e.preventDefault();
    setDragging(false);
    if (disabled) return;
    const file = e.dataTransfer.files[0];
    if (file) onFile(file);
  }

  function handleChange(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (file) onFile(file);
    e.target.value = '';
  }

  return (
    <div
      onClick={() => !disabled && inputRef.current?.click()}
      onDragOver={(e) => { e.preventDefault(); if (!disabled) setDragging(true); }}
      onDragLeave={() => setDragging(false)}
      onDrop={handleDrop}
      className={`
        relative flex flex-col items-center justify-center gap-3 p-8
        border-2 border-dashed rounded-2xl cursor-pointer transition-all select-none
        ${dragging ? 'border-amber-400 bg-amber-400/10 scale-[1.01]' : 'border-zinc-700 bg-zinc-900/50 hover:border-zinc-500 hover:bg-zinc-800/50'}
        ${disabled ? 'opacity-50 cursor-not-allowed' : ''}
      `}
    >
      <input
        ref={inputRef}
        type="file"
        accept={accept}
        className="hidden"
        onChange={handleChange}
        disabled={disabled}
      />
      <span className="text-4xl">{icon}</span>
      <div className="text-center">
        <p className="text-sm font-semibold text-zinc-200">{label}</p>
        <p className="text-xs text-zinc-500 mt-1">{hint}</p>
      </div>
      <span className="text-xs text-amber-500 font-medium">Click or drag & drop</span>
    </div>
  );
}
