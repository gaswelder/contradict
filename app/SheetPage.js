import React from "react";
import styled from "styled-components";
import { Card } from "./Card";
import Resource from "./Resource";
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
  return (
    <Resource getPromise={() => api.sheet(dictID)}>
      {(data) => (
        <ContainerDiv>
          {data.map((tuple) => (
            <Card card={tuple} show key={tuple.id} />
          ))}
        </ContainerDiv>
      )}
    </Resource>
  );
};
