import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { Card } from "./Card";
import { useAPI } from "./withAPI";

const ContainerDiv = styled.div`
  display: flex;
  flex-wrap: wrap;
  & > div {
    flex-basis: 16em;
    flex-grow: 1;
    margin: 0;
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
        <Card
          card={tuple}
          show
          key={tuple.id}
          onChange={(newCard) => {
            api.updateEntry(dictID, tuple.id, { q: newCard.q, a: newCard.a });
            setData(
              data.map((x) =>
                x.id == tuple.id ? { ...x, q: newCard.q, a: newCard.a } : x
              )
            );
          }}
        />
      ))}
    </ContainerDiv>
  );
};
