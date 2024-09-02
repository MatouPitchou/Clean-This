export function formatDuration(seconds) {
    if (!seconds && seconds !== 0) return 'N/A';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secondsLeft = seconds % 60;

    return [
      hours > 0 ? `${hours}h` : '',
      minutes > 0 ? `${minutes}m` : '',
      `${secondsLeft.toFixed(0)}s`
    ].filter(Boolean).join(' ');
  }
