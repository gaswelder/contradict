import { useEffect, useState } from "react";

export const usePromise = (getPromise) => {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [data, setData] = useState(undefined);
  useEffect(() => {
    getPromise()
      .then(setData)
      .catch(setError)
      .finally(() => setLoading(false));
  }, []);
  return { data, error, loading };
};
