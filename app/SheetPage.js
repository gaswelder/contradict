import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { CardSources } from "./CardSources";
import { useAPI } from "./withAPI";

const ContainerDiv = styled.div`
  display: flex;
  flex-wrap: wrap;
  & > div {
    flex-basis: 10em;
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
  return (
    <ContainerDiv>
      {data.map((tuple) => (
        <Div key={tuple.id}>
          <p className="h">{tuple.q}</p>
          <p>{tuple.a}</p>
          <CardSources card={tuple} />
        </Div>
      ))}
    </ContainerDiv>
  );
};
