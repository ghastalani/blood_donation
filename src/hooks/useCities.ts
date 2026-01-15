import { useState, useEffect } from 'react';
import { CITIES, City } from '@/constants/cities';

export const useCities = () => {
  const [cities, setCities] = useState<City[]>(CITIES);
  const [loading, setLoading] = useState(false);

  return { cities, loading, error: null };
};
