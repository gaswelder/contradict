import React from "react";

function CorrectTable(props) {
  const { results } = props;

  return (
    <table>
      <thead>
        <tr>
          <th>Q</th>
          <th>A</th>
          <th />
        </tr>
      </thead>
      <tbody>
        {results.map(r => (
          <tr key={r.question.id}>
            <td>{r["question"]["q"]}</td>
            <td>{r["question"]["a"]}</td>
            <td>ok</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export default CorrectTable;
