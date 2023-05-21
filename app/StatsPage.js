import React, { useEffect, useState } from "react";
import { useAPI } from "./withAPI";

export const StatsPage = ({ dictID }) => {
  const { api } = useAPI();
  const [dict, setDict] = useState(null);
  const [error, setError] = useState(null);
  useEffect(() => {
    api.dict(dictID).then(setDict).catch(setError);
  }, []);
  if (error) {
    return error.message;
  }
  if (!dict) {
    return "loading";
  }
  return (
    <>
      <h2>{dict.name}</h2>
      <p>Total cards: {dict.stats.total}</p>
      <p>In progress: {dict.stats.inProgress}</p>
      <p>Finished: {dict.stats.finished}</p>
      <table>
        <tr>
          <th>slot</th>
          <th>successes</th>
          <th>fails</th>
          <th>% success</th>
        </tr>
        {[0, 1, 2, 3, 4, 5, 6, 7, 8, 9].map((slot) => {
          const succ = dict.stats.transitions[`${slot}-${slot + 1}`] || 0;
          const fail =
            dict.stats.transitions[slot == 0 ? `0-0` : `${slot + 1}-${slot}`] ||
            0;
          return (
            <tr key={slot}>
              <td>{slot}</td>
              <td>{succ}</td>
              <td>{fail}</td>
              <td>
                {succ + fail > 0 && ((succ / (succ + fail)) * 100).toFixed(1)}
              </td>
            </tr>
          );
        })}
      </table>
    </>
  );
};
