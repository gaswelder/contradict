import React from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import AddEntriesPage from "./pages/AddEntriesPage";
import EntryPage from "./pages/EntryPage";
import LoginPage from "./pages/LoginPage";
import MenuPage from "./pages/MenuPage";
import TestPage from "./pages/TestPage/TestPage";
import Export from "./pages/Export";
import DictPage from "./pages/DictPage";
import { ResultsPage } from "./pages/ResultsPage";
import { Page } from "./components/Page";
import { RepetitionsPage } from "./pages/RepetitionsPage";

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
    return (
      <BrowserRouter>
        <Switch>
          <Route exact path="/" component={page(MenuPage)} />
          <Route path="/:id/test" component={page(TestPage)} />
          <Route path="/:id/repetitions" component={page(RepetitionsPage)} />
          <Route path="/:id/results" component={page(ResultsPage)} />
          <Route path="/:id/add" component={page(AddEntriesPage)} />
          <Route path="/dicts/:id" component={page(DictPage)} />
          <Route path="/entries/:id" component={page(EntryPage)} />
          <Route path="/login" component={page(LoginPage, false)} />
          <Route path="/export" component={page(Export)} />
          <Route component={page(() => "Not Found")} />
        </Switch>
      </BrowserRouter>
    );
  }
}

export default Main;
