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

export const Resource = ({ getPromise, children }) => {
  const { data, error, loading } = usePromise(getPromise);
  if (loading) {
    return "Loading";
  }
  if (error) {
    return "Error: " + error.toString();
  }
  return children(data);
};

export default Resource;
