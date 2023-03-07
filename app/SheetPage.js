import React from "react";
import styled from "styled-components";
import Resource from "./Resource";
import { urlTitle } from "./url-title";
import { useAPI } from "./withAPI";

const ContainerDiv = styled.div`
  columns: 20em;
`;

export const SheetPage = ({ dictID }) => {
  const { api } = useAPI();
  return (
    <Resource getPromise={() => api.test(dictID)}>
      {(data) => (
        <ContainerDiv>
          {data.tuples1.map((tuple) => {
            return (
              <article key={tuple.id}>
                {tuple.q} - {tuple.a}
                <ul>
                  {tuple.urls.map((url) => (
                    <li key={url}>
                      <a target="_blank" rel="noreferrer" href={url}>
                        {urlTitle(url)}
                      </a>
                    </li>
                  ))}
                </ul>
              </article>
            );
          })}
        </ContainerDiv>
      )}
    </Resource>
  );
};
