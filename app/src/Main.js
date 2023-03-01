import React from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import AddEntriesPage from "./pages/AddEntriesPage";
import LoginPage from "./pages/LoginPage";
import MenuPage from "./pages/MenuPage";
import TestPage from "./pages/TestPage/TestPage";
import Export from "./pages/Export";
import { ResultsPage } from "./pages/ResultsPage";
import { Page } from "./components/Page";
import { RepetitionsPage } from "./pages/RepetitionsPage";
import { DictPage } from "./pages/DictPage";
import { ROOT_PATH } from "./api";

function page(Component, header = true) {
  const wrappedPage = (props) => (
    <Page header={header}>
      <Component {...props} />
    </Page>
  );
  return wrappedPage;
}

class Main extends React.Component {
  render() {
    const R = ROOT_PATH;
    return (
      <BrowserRouter>
        <Switch>
          <Route exact path={`${R}`} component={page(MenuPage)} />
          <Route
            path={`${R}:id/test`}
            component={page(({ match }) => {
              return <TestPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/repetitions`}
            component={page(({ match }) => {
              return <RepetitionsPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/results`}
            component={page(({ match }) => {
              return <ResultsPage id={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/add`}
            component={page(({ match }) => {
              return <AddEntriesPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}dicts/:id`}
            component={page(({ match }) => {
              return <DictPage dictID={match.params.id} />;
            })}
          />
          <Route path={`${R}login`} component={page(LoginPage, false)} />
          <Route path={`${R}export`} component={page(Export)} />
          <Route component={page(() => "Not Found")} />
        </Switch>
      </BrowserRouter>
    );
  }
}

export default Main;
