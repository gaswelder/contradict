import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { useAPI } from "./withAPI";

const ContainerDiv = styled.div`
  display: flex;
  flex-wrap: wrap;
  & > div {
    flex-basis: 20em;
    flex-grow: 1;
    margin: 0;
  }
`;

const Div = styled.div`
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  padding: 4px;
  border-top: 1px solid #ccc;
  border-left: 1px solid #eee;
  text-align: center;
  & .h {
    font-weight: bold;
    margin-bottom: 0;
  }
  & p + p {
    margin-top: 0;
  }
`;

export const SheetPage = ({ dictID }) => {
  const { api } = useAPI();
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);
  useEffect(() => {
    api.sheet(dictID).then(setData).catch(setError);
  }, []);
  if (error) {
    return error.message;
  }
  if (!data) {
    return "loading";
  }
  const batches = chunk(data);
  return (
    <ContainerDiv>
      {batches.map((batch, i) => {
        return (
          <Div key={i}>
            {batch.map((tuple) => {
              return (
                <p>
                  {tuple.q} &ndash; {tuple.a}
                </p>
              );
            })}
          </Div>
        );
      })}
    </ContainerDiv>
  );
};

const chunk = (xs) => {
  const n = 10;
  if (xs.length <= n) {
    return [xs];
  }
  return [xs.slice(0, 10), ...chunk(xs.slice(10))];
};
