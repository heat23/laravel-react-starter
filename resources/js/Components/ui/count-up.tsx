import { useEffect, useRef, useState } from "react";

interface CountUpProps {
  end: number;
  duration?: number;
  format?: (n: number) => string;
}

export function CountUp({ end, duration = 600, format = (n) => n.toLocaleString() }: CountUpProps) {
  const [value, setValue] = useState(end);
  const prevEnd = useRef(end);

  useEffect(() => {
    if (prevEnd.current === end) {
      setValue(end);
      return;
    }
    const from = prevEnd.current;
    prevEnd.current = end;

    const startTime = performance.now();
    let cancelled = false;

    function step(now: number) {
      if (cancelled) return;
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      setValue(Math.round(from + eased * (end - from)));
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);

    return () => {
      cancelled = true;
    };
  }, [end, duration]);

  return <>{format(value)}</>;
}
