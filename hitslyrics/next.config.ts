import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Allow serving uploaded files from /public/uploads
  images: {
    remotePatterns: [],
    // Unoptimized for local uploads (blob URLs and local paths)
    unoptimized: true,
  },
  // Increase body size limit for audio uploads
  experimental: {
    serverActions: {
      bodySizeLimit: '105mb',
    },
  },
};

export default nextConfig;
